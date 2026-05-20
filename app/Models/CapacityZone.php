<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'zone_identifier', 'max_capacity', 'warning_threshold', 'critical_threshold', 'current_count', 'is_active'])]
class CapacityZone extends Model
{
  use HasFactory;

  protected $casts = [
    'is_active' => 'boolean',
  ];

  /**
   * Get the occupancy percentage.
   */
  public function getOccupancyPercentageAttribute(): int
  {
    if ($this->max_capacity === 0) {
      return 0;
    }
    return (int) round(($this->current_count / $this->max_capacity) * 100);
  }

  /**
   * Check if the zone is at warning threshold.
   */
  public function isAtWarning(): bool
  {
    return $this->occupancy_percentage >= $this->warning_threshold;
  }

  /**
   * Check if the zone is at critical threshold.
   */
  public function isAtCritical(): bool
  {
    return $this->occupancy_percentage >= $this->critical_threshold;
  }
}