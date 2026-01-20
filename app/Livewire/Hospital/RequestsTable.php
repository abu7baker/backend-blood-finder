<?php

namespace App\Livewire\Hospital;

use App\Models\BloodRequest;
use Livewire\Component;

class RequestsTable extends Component
{
    public string $search = '';

    public function render()
    {
        $hospital = auth()->user()?->hospital;

        $stats = [
            'urgent' => 0,
            'pending' => 0,
            'completed' => 0,
        ];

        if (!$hospital) {
            return view('livewire.hospital.requests-table', [
                'requests' => collect(),
                'stats' => $stats,
                'hasHospital' => false,
            ]);
        }

        $stats = [
            'urgent' => BloodRequest::where('hospital_id', $hospital->id)
                ->where('priority', 'urgent')
                ->count(),
            'pending' => BloodRequest::where('hospital_id', $hospital->id)
                ->where('status', 'pending')
                ->count(),
            'completed' => BloodRequest::where('hospital_id', $hospital->id)
                ->where('status', 'completed')
                ->count(),
        ];

        $query = BloodRequest::with('requester')
            ->where('hospital_id', $hospital->id)
            ->latest();

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('patient_name', 'like', $term)
                    ->orWhere('blood_type', 'like', $term)
                    ->orWhereHas('requester', function ($requester) use ($term) {
                        $requester->where('full_name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        }

        $requests = $query->get();

        return view('livewire.hospital.requests-table', [
            'requests' => $requests,
            'stats' => $stats,
            'hasHospital' => true,
        ]);
    }
}
