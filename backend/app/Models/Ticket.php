<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id', 'requester_id', 'assignee_id',
        'subject', 'description', 'status', 'priority', 'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function organization() { return $this->belongsTo(Organization::class); }
    public function requester()     { return $this->belongsTo(User::class, 'requester_id'); }
    public function assignee()     { return $this->belongsTo(User::class, 'assignee_id'); }
    public function comments()     { return $this->hasMany(Comment::class); }
}