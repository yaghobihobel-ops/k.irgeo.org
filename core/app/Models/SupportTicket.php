<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{

    public function exportColumns(): array
    {
        return  [

            'user_id' => [
                'name' => "User",
                'callback' => function ($item) {
                    if ($item->user_id != 0 && $item->user) {
                        return $item->user->fullname;
                    } elseif ($item->agent_id != 0 && $item->agent) {
                        return $item->agent->fullname;
                    } elseif ($item->merchant_id != 0 && $item->merchant) {
                        return $item->merchant->fullname;
                    }
                    return 'N/A';
                }
            ],
            'subject',
            'status' => [
                'callback' => function ($item) {
                    return strip_tags($item->statusBadge);
                }
            ],
            'priority' => [
                'callback' => function ($item) {
                    return strip_tags($item->priorityBadge);
                }
            ],
            'last_reply' => [
                'callback' => function ($item) {
                    return showDateTime($item->last_reply,lang:'en');
                }
            ]
        ];
    }

    public function fullname(): Attribute
    {
        return new Attribute(
            get: fn() => $this->name,
        );
    }

    public function username(): Attribute
    {
        return new Attribute(
            get: fn() => $this->email,
        );
    }

    public function statusBadge(): Attribute
    {
        return new Attribute(function () {
            $html = '';
            if ($this->status == Status::TICKET_OPEN) {
                $html = '<span class="badge badge--success">' . trans("Open") . '</span>';
            } elseif ($this->status == Status::TICKET_ANSWER) {
                $html = '<span class="badge badge--primary">' . trans("Answered") . '</span>';
            } elseif ($this->status == Status::TICKET_REPLY) {
                $html = '<span class="badge badge--warning">' . trans("Customer Reply") . '</span>';
            } elseif ($this->status == Status::TICKET_CLOSE) {
                $html = '<span class="badge badge--dark">' . trans("Closed") . '</span>';
            }
            return $html;
        });
    }
    public function priorityBadge(): Attribute
    {
        return new Attribute(function () {
            $html = '';
            if ($this->priority == Status::PRIORITY_LOW) {
                $html = '<span class="badge badge--dark">' . trans("Low") . '</span>';
            } elseif ($this->priority == Status::PRIORITY_MEDIUM) {
                $html = '<span class="badge badge--warning">' . trans("Medium") . '</span>';
            } elseif ($this->priority == Status::PRIORITY_HIGH) {
                $html = '<span class="badge badge--danger">' . trans("High") . '</span>';
            }
            return $html;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function supportMessage()
    {
        return $this->hasMany(SupportMessage::class);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [Status::TICKET_OPEN, Status::TICKET_REPLY]);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', Status::TICKET_CLOSE);
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', Status::TICKET_ANSWER);
    }

    public function scopeUserTicket($query)
    {
        return $query->where('agent_id', '=', 0)->where('merchant_id', '=', 0);
    }

    public function scopeAgentTicket($query)
    {
        return $query->where('agent_id','!=',0);
    }
    
    public function scopeMerchantTicket($query)
    {
        return $query->where('merchant_id','!=',0);
    }
   
}
