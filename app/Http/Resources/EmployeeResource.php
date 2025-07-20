<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'address' => $this->address,
            'marital_status' => $this->marital_status,
            'education' => $this->education,
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'contract_start' => $this->contract_start?->format('Y-m-d'),
            'contract_end' => $this->contract_end?->format('Y-m-d'),
            'salary' => $this->salary,
            'position' => $this->position,
            'department' => $this->department,
            'status' => $this->status,
            'notes' => $this->notes,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relation' => $this->emergency_contact_relation,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relationships
            'location' => $this->whenLoaded('location', function () {
                return [
                    'id' => $this->location->id,
                    'name' => $this->location->name,
                    'code' => $this->location->code,
                ];
            }),
            'pkwt' => $this->whenLoaded('pkwt', function () {
                return [
                    'id' => $this->pkwt->id,
                    'name' => $this->pkwt->name,
                ];
            }),
            'employee_contract' => $this->whenLoaded('employeeContract', function () {
                return [
                    'id' => $this->employeeContract->id,
                    'name' => $this->employeeContract->name,
                    'type' => $this->employeeContract->type,
                ];
            }),
            
            // Additional computed fields
            'age' => $this->birth_date ? $this->birth_date->age : null,
            'years_of_service' => $this->hire_date ? $this->hire_date->diffInYears(now()) : null,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
        ];
    }
    
    private function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'terminated' => 'Terminated',
            'resigned' => 'Resigned',
            default => 'Unknown'
        };
    }
    
    private function getStatusColor(): string
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'terminated' => 'danger',
            'resigned' => 'secondary',
            default => 'secondary'
        };
    }
}
