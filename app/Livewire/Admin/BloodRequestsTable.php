<?php

namespace App\Livewire\Admin;

use App\Models\BloodRequest;
use Livewire\Component;
use Livewire\WithPagination;

class BloodRequestsTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $priority = 'all';

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->status = request('status', 'all');
        $this->priority = request('priority', 'all');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $stats = [
            'critical' => BloodRequest::where('priority', 'critical')->count(),
            'pending' => BloodRequest::where('status', 'pending')->count(),
            'completed' => BloodRequest::where('status', 'completed')->count(),
        ];

        $query = BloodRequest::with(['requester', 'hospital'])->latest();

        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->priority && $this->priority !== 'all') {
            $query->where('priority', $this->priority);
        }

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('patient_name', 'like', $term)
                    ->orWhere('blood_type', 'like', $term)
                    ->orWhereHas('hospital', function ($hospital) use ($term) {
                        $hospital->where('name', 'like', $term);
                    })
                    ->orWhereHas('requester', function ($requester) use ($term) {
                        $requester->where('full_name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        }

        $requests = $query->paginate(20);

        return view('livewire.admin.blood-requests-table', compact('requests', 'stats'));
    }
}
