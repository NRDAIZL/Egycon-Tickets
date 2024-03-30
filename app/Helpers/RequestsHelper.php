<?php

namespace App\Helpers;

class RequestsHelper {

    public static function SearchRequests($base_requests, $search_query) {

        // filter if status is null and no reciept
        $base_requests = $base_requests->where(function($query){
                return $query->where('posts.status','!=', null)->orWhere('picture', '!=', "")->orWhere(function ($q) {
                    return $q->where('picture', null)->orWhere('payment_method', 'reservation');
                });
        });

        if($search_query == null){
            return $base_requests->orderBy('created_at',"DESC");
        }

        $base_requests = $base_requests->where(function ($parent_query) use ($search_query) {
            return $parent_query
                ->orWhere('email', 'like', '%' . $search_query . '%')
                ->orWhere('phone_number', 'like', '%' . $search_query . '%')
                ->orWhere('id', 'like', '%' . $search_query . '%')
                ->orWhere('order_reference_id', 'like', '%' . $search_query . '%')
                ->orWhere('name', 'like', '%' . $search_query . '%')
                ->orWhereHas('ticket', function ($query) use ($search_query) {
                    return $query->whereHas('ticket_type', function ($child_query) use ($search_query) {
                        return $child_query->where('name', 'like', '%' . $search_query . '%');
                    });
                })->orWhereHas('ticket', function ($query) use ($search_query) {
                    return $query->whereHas('sub_ticket_type', function ($child_query) use ($search_query) {
                        return $child_query->where('name', 'like', '%' . $search_query . '%');
                    });
                });
        });

        return $base_requests;
    }
    
}
