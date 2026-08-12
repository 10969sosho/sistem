<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Finance;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Data customer dan project dari data.txt
        $customerProjects = [
            'PT Mahkota Kargo Logistik' => ['Google Maps', 'Google Maps Semarang'],
            'Hakusa Edu Japan' => ['Website', 'Maintenance'],
            'PT Indira Kusuma Pratama' => ['Website', 'Maintenance'],
            'PT Harvest Global Niaga' => ['Website', 'Penambahan Platform'],
            'Yayasan Manarul Ilmi ITS' => ['Website', 'Maintenance & Domain/Hosting'],
            'PT Dara Boga Nusantara' => ['Website'],
            'CV Santo' => ['Website'],
            'CV Hartin Kadjar Makmur' => ['Website'],
            'PT Pemburu Property Bali' => ['Website'],
            'Bu Vania' => ['Formula Excel', 'Sistem Payroll'],
            'CV Keneas Solusi Wisaya' => ['Modifikasi Website Registration'],
            'PT Parama Asia Sejahtera' => ['Website'],
            'PT Abadi Mitra Perkasa' => ['Setup Email Perusahaan', 'Domain'],
            'Bpk Tjendrawan' => ['Sistem Pencatatan Operasional'],
            'CV Jowimar Mandiri Makmur' => ['Aplikasi E-Commerce'],
            'L Kids Official' => ['Website', 'Maintenance'],
            'Djaya Mandiri Teknik' => ['Website'],
        ];

        // Data pembayaran dari payment.txt
        $payments = [
            'PT Mahkota Kargo Logistik' => [
                'Google Maps' => ['total' => 500000, 'pelunasan' => 500000],
                'Google Maps Semarang' => ['total' => 500000, 'pelunasan' => 500000],
            ],
            'Hakusa Edu Japan' => [
                'Website' => [
                    'total' => 3500000,
                    'termin1' => 1500000,
                    'termin2' => 1000000,
                    'termin3' => 1000000,
                ],
                'Maintenance' => ['total' => 500000, 'pelunasan' => 500000],
            ],
            'PT Indira Kusuma Pratama' => [
                'Website' => [
                    'total' => 24500000,
                    'termin1' => 12000000,
                    'termin2' => 12500000,
                ],
                'Maintenance' => ['total' => 2000000, 'pelunasan' => 2000000],
            ],
            'PT Harvest Global Niaga' => [
                'Website' => [
                    'total' => 25641024,
                    'termin1' => 6410256,
                    'termin2' => 6410256,
                    'termin3' => 12820512,
                ],
                'Penambahan Platform' => ['total' => 1500000, 'pelunasan' => 1500000],
            ],
            'Yayasan Manarul Ilmi ITS' => [
                'Website' => [
                    'total' => 5000000,
                    'termin1' => 3000000,
                    'termin2' => 2000000,
                ],
                'Maintenance & Domain/Hosting' => ['total' => 5000000, 'pelunasan' => 5000000],
            ],
            'PT Dara Boga Nusantara' => [
                'Website' => [
                    'total' => 1500000,
                    'termin1' => 750000,
                    'termin2' => 750000,
                ],
            ],
            'CV Santo' => [
                'Website' => [
                    'total' => 6000000,
                    'termin1' => 6000000,
                ],
            ],
            'CV Hartin Kadjar Makmur' => [
                'Website' => [
                    'total' => 1500000,
                    'termin1' => 750000,
                    'termin2' => 750000,
                ],
            ],
            'PT Pemburu Property Bali' => [
                'Website' => [
                    'total' => 6800000,
                    'termin1' => 3400000,
                    'termin2' => 3400000,
                ],
            ],
            'Bu Vania' => [
                'Formula Excel' => [
                    'total' => 4300000,
                    'termin1' => 1000000,
                    'termin2' => 2000000,
                    'termin3' => 1000000,
                    'pelunasan' => 300000,
                ],
                'Sistem Payroll' => [
                    'total' => 2500000,
                    'termin1' => 2500000,
                ],
            ],
            'CV Keneas Solusi Wisaya' => [
                'Modifikasi Website Registration' => ['total' => 1500000, 'pelunasan' => 1500000],
            ],
            'PT Parama Asia Sejahtera' => [
                'Website' => [
                    'total' => 16000000,
                    'termin1' => 6400000,
                    'termin2' => 9600000,
                ],
            ],
            'PT Abadi Mitra Perkasa' => [
                'Setup Email Perusahaan' => ['total' => 1500000, 'pelunasan' => 1500000],
                'Domain' => ['total' => 170065, 'pelunasan' => 170065],
            ],
            'Bpk Tjendrawan' => [
                'Sistem Pencatatan Operasional' => ['total' => 4000000, 'pelunasan' => 4000000],
            ],
            'CV Jowimar Mandiri Makmur' => [
                'Aplikasi E-Commerce' => [
                    'total' => 7800000,
                    'termin1' => 1950000,
                    'termin2' => 1950000,
                    'termin3' => 1950000,
                    'pelunasan' => 1950000,
                ],
            ],
            'L Kids Official' => [
                'Website' => ['total' => 0, 'termin1' => 0],
                'Maintenance' => ['total' => 0, 'termin1' => 0],
            ],
            'Djaya Mandiri Teknik' => [
                'Website' => [
                    'total' => 5000000,
                    'termin1' => 1500000,
                    'termin2' => 3500000,
                ],
            ],
        ];

        // Create customers and projects
        foreach ($customerProjects as $customerName => $projects) {
            $customer = Customer::factory()->create([
                'name' => $customerName,
            ]);

            foreach ($projects as $projectName) {
                $project = Project::factory()->create([
                    'customer_id' => $customer->id,
                    'name' => $projectName,
                ]);

                // Create finance data if exists
                if (isset($payments[$customerName][$projectName])) {
                    $payment = $payments[$customerName][$projectName];
                    Finance::create([
                        'project_id' => $project->id,
                        'total' => $payment['total'] ?? 0,
                        'dp' => $payment['dp'] ?? 0,
                        'termin1' => $payment['termin1'] ?? 0,
                        'termin2' => $payment['termin2'] ?? 0,
                        'termin3' => $payment['termin3'] ?? 0,
                        'pelunasan' => $payment['pelunasan'] ?? 0,
                    ]);
                }
            }
        }
    }
}
