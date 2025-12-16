<?php

if (!function_exists("normalizeShipmentStatus")) {
    function normalizeShipmentStatus(string $status): string
    {
        $status = strtoupper(trim($status));

        /* ---------------- PENDING PICKUP ---------------- */
        if (in_array($status, [
            'PICKUP BOOKED',
            'PICKUP RESCHEDULED',
            'PICKUP ERROR',
            'PICKUP EXCEPTION',
            'SHIPMENT BOOKED',
            'HANDOVER TO COURIER',
            'BOX PACKING',
            'FC ALLOCATED',
            'PICKLIST GENERATED',
            'READY TO PACK',
            'PACKED',
            'FC MANIFEST GENERATED',
            'PROCESSED AT WAREHOUSE',
            'REACHED WAREHOUSE',
            'QC FAILED',
            'SELF FULFILLED',
        ])) {
            return 'PENDING_PICKUP';
        }

        /* ---------------- RTO ---------------- */
        if (
            str_contains($status, 'RTO')
        ) {
            return 'RTO';
        }

        /* ---------------- DELIVERED ---------------- */
        if (
            in_array($status, [
                'DELIVERED',
                'PARTIAL_DELIVERED',
                'FULFILLED',
            ])
        ) {
            return 'DELIVERED';
        }

        /* ---------------- CANCELLED ---------------- */
        if (
            in_array($status, [
                'CANCELED',
                'CANCELLED',
                'CANCELLATION REQUESTED',
                'CANCELLED_BEFORE_DISPATCHED',
                'DISPOSED OFF',
                'DESTROYED',
                'DAMAGED',
                'LOST',
                'UNTRACEABLE',
                'ISSUE_RELATED_TO_THE_RECIPIENT',
            ])
        ) {
            return 'CANCELLED';
        }

        /* ---------------- IN TRANSIT ---------------- */
        if (
            str_contains($status, 'IN TRANSIT') ||
            str_contains($status, 'OUT FOR DELIVERY') ||
            str_contains($status, 'OUT FOR PICKUP') ||
            str_contains($status, 'SHIPPED') ||
            str_contains($status, 'IN FLIGHT') ||
            str_contains($status, 'MISROUTED') ||
            str_contains($status, 'DELAYED') ||
            str_contains($status, 'CUSTOM CLEARED') ||
            str_contains($status, 'REACHED')
        ) {
            return 'IN_TRANSIT';
        }

        /* ---------------- FALLBACK ---------------- */
        return $status;
    }

}
