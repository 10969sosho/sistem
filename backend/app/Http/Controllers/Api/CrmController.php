<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeLeadStatusRequest;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\LeadResource;
use App\Http\Resources\OpportunityResource;
use App\Services\CrmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function __construct(private CrmService $service) {}

    public function dashboard(Request $request): JsonResponse
    {
        $data = $this->service->dashboard($request->user());
        $data['recent_activities'] = ActivityResource::collection($data['recent_activities'])->resolve();
        $data['recent_leads'] = LeadResource::collection($data['recent_leads'])->resolve();
        return response()->json(['data' => $data]);
    }

    public function leads(Request $request): mixed { return LeadResource::collection($this->service->leads($request->user(), $request->only(['status', 'search', 'per_page']))); }
    public function lead(Request $request, int $id): LeadResource { return new LeadResource($this->service->findLead($request->user(), $id)); }
    public function storeLead(StoreLeadRequest $request): JsonResponse { return response()->json(['message' => 'Lead berhasil ditambahkan.', 'data' => new LeadResource($this->service->createLead($request->user(), $request->validated()))], 201); }
    public function updateLead(UpdateLeadRequest $request, int $id): JsonResponse { return response()->json(['message' => 'Lead diperbarui.', 'data' => new LeadResource($this->service->updateLead($request->user(), $id, $request->validated()))]); }
    public function status(ChangeLeadStatusRequest $request, int $id): JsonResponse { return response()->json(['message' => 'Status pipeline diperbarui.', 'data' => new LeadResource($this->service->changeLeadStatus($request->user(), $id, $request->validated()))]); }
    public function activity(StoreActivityRequest $request, int $id): JsonResponse { return response()->json(['message' => 'Aktivitas dicatat.', 'data' => new LeadResource($this->service->addActivity($request->user(), $id, $request->validated()))]); }
    public function destroyLead(Request $request, int $id): JsonResponse { $this->service->deleteLead($request->user(), $id); return response()->json(['message' => 'Lead dihapus.']); }
    public function activities(Request $request): mixed { return ActivityResource::collection($this->service->activities($request->user(), $request->only(['lead_id', 'per_page']))); }
    public function opportunities(Request $request): mixed { return OpportunityResource::collection($this->service->opportunities($request->user(), $request->only(['offers', 'stage', 'search', 'per_page']))); }
    public function opportunity(Request $request, int $id): OpportunityResource { return new OpportunityResource($this->service->findOpportunity($request->user(), $id)); }
    public function storeOpportunity(StoreOpportunityRequest $request): JsonResponse { return response()->json(['message' => 'Penawaran berhasil dibuat.', 'data' => new OpportunityResource($this->service->createOpportunity($request->user(), $request->validated()))], 201); }
    public function updateOpportunity(UpdateOpportunityRequest $request, int $id): JsonResponse { return response()->json(['message' => 'Pipeline diperbarui.', 'data' => new OpportunityResource($this->service->updateOpportunity($request->user(), $id, $request->validated()))]); }
    public function destroyOpportunity(Request $request, int $id): JsonResponse { $this->service->deleteOpportunity($request->user(), $id); return response()->json(['message' => 'Penawaran dihapus.']); }
}
