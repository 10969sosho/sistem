<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CrmService
{
    public function dashboard(User $user): array
    {
        $leads = $this->leadQuery($user)->with('opportunities')->get();
        $opportunities = Opportunity::where('user_id', $user->id)->get();

        $sourceStats = $leads->groupBy(fn (Lead $lead) => $lead->source ?: 'other')->map(function ($items) {
            return [
                'leads' => $items->count(),
                'interested' => $items->whereIn('status', ['interested', 'discussion', 'offer_sent', 'negotiation', 'deal'])->count(),
                'offers' => $items->whereIn('status', ['offer_sent', 'negotiation', 'deal'])->count(),
                'deals' => $items->where('status', 'deal')->count(),
                'revenue' => (float) $items->flatMap->opportunities->where('stage', 'deal')->sum('value'),
            ];
        });

        return [
            'status_counts' => collect(Lead::STATUSES)->mapWithKeys(fn (string $status) => [$status => $leads->where('status', $status)->count()]),
            'pipeline_value' => (float) $opportunities->whereNotIn('stage', ['deal', 'lost'])->sum('value'),
            'revenue' => (float) $opportunities->where('stage', 'deal')->sum('value'),
            'source_stats' => $sourceStats,
            'recent_activities' => Activity::where('user_id', $user->id)->with('lead')->latest()->limit(8)->get(),
            'urgent_tasks' => Task::whereHas('lead', fn (Builder $query) => $query->where('user_id', $user->id))
                ->where('status', '!=', 'done')->whereDate('deadline', '<=', today())->with('lead')->orderBy('deadline')->limit(8)->get(),
            'recent_leads' => $this->leadQuery($user)->latest('entered_at')->limit(6)->get(),
            'customers_count' => Customer::where(function (Builder $query) use ($user) { $query->where('user_id', $user->id)->orWhereNull('user_id'); })->count(),
        ];
    }

    public function leads(User $user, array $filters): LengthAwarePaginator
    {
        $query = $this->leadQuery($user)->with('opportunities');
        if (! empty($filters['status']) && in_array($filters['status'], Lead::STATUSES, true)) $query->where('status', $filters['status']);
        if (! empty($filters['search'])) $query->where(fn (Builder $builder) => $builder->where('name', 'like', "%{$filters['search']}%")->orWhere('company', 'like', "%{$filters['search']}%")->orWhere('phone', 'like', "%{$filters['search']}%"));
        return $query->latest('entered_at')->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    public function findLead(User $user, int $id): Lead
    {
        return $this->leadQuery($user)->with(['activities' => fn ($query) => $query->latest(), 'opportunities' => fn ($query) => $query->latest(), 'customer'])->findOrFail($id);
    }

    public function createLead(User $user, array $data): Lead
    {
        return DB::transaction(function () use ($user, $data) {
            $lead = Lead::create([...$data, 'user_id' => $user->id, 'status' => 'new']);
            Activity::create(['user_id' => $user->id, 'lead_id' => $lead->id, 'type' => 'lead_created', 'description' => 'Lead masuk dari '.(Lead::SOURCES[$lead->source] ?? $lead->source).'.']);
            Task::create(['lead_id' => $lead->id, 'title' => "Follow-up {$lead->name}", 'type' => 'maintenance', 'priority' => 'high', 'status' => 'todo', 'deadline' => $lead->entered_at, 'notes' => 'Hubungi lead baru dan catat hasil percakapan di timeline.']);
            return $lead->load('opportunities');
        });
    }

    public function updateLead(User $user, int $id, array $data): Lead
    {
        $lead = $this->ownedLead($user, $id);
        $lead->update($data);
        $this->activity($user, $lead, 'lead_updated', 'Data lead diperbarui.');
        return $this->findLead($user, $id);
    }

    public function changeLeadStatus(User $user, int $id, array $data): Lead
    {
        return DB::transaction(function () use ($user, $id, $data) {
            $lead = $this->ownedLead($user, $id);
            $oldStatus = $lead->status;
            $lead->update($data);
            if ($lead->status === 'deal') $this->convertToCustomer($user, $lead);
            if ($oldStatus !== $lead->status) $this->activity($user, $lead, 'status_changed', 'Status berubah dari '.(Lead::STATUS_LABELS[$oldStatus] ?? $oldStatus).' ke '.(Lead::STATUS_LABELS[$lead->status] ?? $lead->status).'.');
            return $this->findLead($user, $lead->id);
        });
    }

    public function addActivity(User $user, int $id, array $data): Lead
    {
        $lead = $this->ownedLead($user, $id);
        $this->activity($user, $lead, $data['type'], $data['description']);
        if ($lead->status === 'new' && $data['type'] !== 'note') {
            $lead->update(['status' => 'contacted', 'replied_at' => now()]);
            $this->activity($user, $lead, 'status_changed', 'Status otomatis berubah ke Contacted setelah aktivitas pertama.');
        }
        return $this->findLead($user, $id);
    }

    public function deleteLead(User $user, int $id): void { $this->ownedLead($user, $id)->delete(); }

    public function activities(User $user, array $filters): LengthAwarePaginator
    {
        $query = Activity::where('user_id', $user->id)->with('lead')->latest();
        if (! empty($filters['lead_id'])) $query->where('lead_id', $filters['lead_id']);
        return $query->paginate($filters['per_page'] ?? 20)->withQueryString();
    }

    public function opportunities(User $user, array $filters): LengthAwarePaginator
    {
        $query = Opportunity::where('user_id', $user->id)->with(['lead', 'customer'])->latest();
        if (! empty($filters['offers'])) $query->whereIn('stage', ['offer_sent', 'negotiation']);
        if (! empty($filters['stage']) && in_array($filters['stage'], Lead::STATUSES, true)) $query->where('stage', $filters['stage']);
        if (! empty($filters['search'])) $query->where(fn (Builder $builder) => $builder->where('title', 'like', "%{$filters['search']}%")->orWhereHas('lead', fn (Builder $lead) => $lead->where('name', 'like', "%{$filters['search']}%")));
        return $query->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    public function findOpportunity(User $user, int $id): Opportunity
    {
        return Opportunity::where('user_id', $user->id)->with(['lead', 'customer'])->findOrFail($id);
    }

    public function createOpportunity(User $user, array $data): Opportunity
    {
        return DB::transaction(function () use ($user, $data) {
            $lead = $this->ownedLead($user, $data['lead_id']);
            $opportunity = Opportunity::create([...$data, 'stage' => 'offer_sent', 'probability' => 60, 'proposal_sent_at' => $data['offer_date'], 'user_id' => $user->id]);
            $lead->update(['status' => 'offer_sent', 'estimated_value' => $data['value']]);
            $this->activity($user, $lead, 'offer_sent', 'Penawaran '.number_format((float) $opportunity->value, 0, ',', '.').' dikirim.');
            return $this->findOpportunity($user, $opportunity->id);
        });
    }

    public function updateOpportunity(User $user, int $id, array $data): Opportunity
    {
        return DB::transaction(function () use ($user, $id, $data) {
            $opportunity = $this->findOpportunity($user, $id);
            $oldStage = $opportunity->stage;
            $opportunity->update([...$data, 'proposal_sent_at' => $data['offer_date'] ?? $opportunity->proposal_sent_at, 'deal_date' => $data['stage'] === 'deal' ? today() : $opportunity->deal_date, 'probability' => $data['stage'] === 'deal' ? 100 : ($data['stage'] === 'lost' ? 0 : $opportunity->probability)]);
            if ($opportunity->lead) {
                $opportunity->lead->update(['status' => $data['stage'], 'estimated_value' => $data['value'], 'deal_date' => $data['stage'] === 'deal' ? today() : $opportunity->lead->deal_date]);
                if ($data['stage'] === 'deal') $this->convertToCustomer($user, $opportunity->lead, $opportunity);
                if ($oldStage !== $data['stage']) $this->activity($user, $opportunity->lead, 'status_changed', 'Status penawaran berubah ke '.(Lead::STATUS_LABELS[$data['stage']] ?? $data['stage']).'.');
            }
            return $this->findOpportunity($user, $id);
        });
    }

    public function deleteOpportunity(User $user, int $id): void { $this->findOpportunity($user, $id)->delete(); }

    private function leadQuery(User $user): Builder { return Lead::where('user_id', $user->id); }
    private function ownedLead(User $user, int $id): Lead { return $this->leadQuery($user)->findOrFail($id); }
    private function activity(User $user, Lead $lead, string $type, string $description): Activity { return Activity::create(['user_id' => $user->id, 'lead_id' => $lead->id, 'type' => $type, 'description' => $description]); }

    private function convertToCustomer(User $user, Lead $lead, ?Opportunity $opportunity = null): void
    {
        $customer = Customer::where('whatsapp', $lead->phone)->first();
        $customer ??= Customer::create(['name' => $lead->name, 'company' => $lead->company, 'whatsapp' => $lead->phone, 'email' => $lead->email, 'status' => 'active', 'user_id' => $user->id]);
        if (! $customer->user_id) $customer->update(['user_id' => $user->id]);
        $lead->update(['customer_id' => $customer->id, 'deal_date' => today()]);
        $opportunity ??= $lead->opportunities()->latest()->first();
        if (! $opportunity) {
            $opportunity = Opportunity::create(['title' => $lead->requirement ?: 'Project '.$lead->name, 'lead_id' => $lead->id, 'value' => $lead->estimated_value ?? 0, 'stage' => 'deal', 'probability' => 100, 'deal_date' => today(), 'user_id' => $user->id]);
        }
        $opportunity->update(['customer_id' => $customer->id, 'stage' => 'deal', 'probability' => 100, 'deal_date' => today()]);
    }
}
