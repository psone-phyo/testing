<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BusController extends Controller
{
    private $busData;

    public function __construct()
    {
        $this->busData = [
            '2025-05-05' => [
                [
                    'id' => 1,
                    'company' => 'JJ Express',
                    'from' => 'Yangon',
                    'to' => 'Mandalay',
                    'type' => 'Normal',
                    'price' => 28000,
                    'seats' => range(1, 21),
                ],
                [
                    'id' => 2,
                    'company' => 'Elite Bus',
                    'from' => 'Yangon',
                    'to' => 'Taunggyi',
                    'type' => 'VIP',
                    'price' => 35000,
                    'seats' => [10, 11, 12, 13, 14, 15, 16, 18, 19, 22],
                ],
                [
                    'id' => 3,
                    'company' => 'Shwe Mandalar',
                    'from' => 'Bagan',
                    'to' => 'Yangon',
                    'type' => 'Normal',
                    'price' => 27000,
                    'seats' => range(1, 21),
                ],
                [
                    'id' => 4,
                    'company' => 'OK Express',
                    'from' => 'Naypyidaw',
                    'to' => 'Mandalay',
                    'type' => 'VIP',
                    'price' => 37000,
                    'seats' => [11, 12, 13, 14, 15, 16, 17, 18, 20, 22],
                ],
                [
                    'id' => 5,
                    'company' => 'JJ Express',
                    'from' => 'Pyin Oo Lwin',
                    'to' => 'Bagan',
                    'type' => 'Normal',
                    'price' => 26000,
                    'seats' => range(1, 21),
                ],
                [
                    'id' => 6,
                    'company' => 'Elite Bus',
                    'from' => 'Mandalay',
                    'to' => 'Yangon',
                    'type' => 'VIP',
                    'price' => 36000,
                    'seats' => [10, 12, 13, 14, 15, 16, 18, 19, 21, 22],
                ]
                ],
            '2025-05-06' => [
                [
                    'id' => 1,
                    'company' => 'JJ Express',
                    'from' => 'Yangon',
                    'to' => 'Mandalay',
                    'type' => 'Normal',
                    'price' => 28000,
                    'seats' => range(1, 21),
                ],
                [
                    'id' => 2,
                    'company' => 'Elite Bus',
                    'from' => 'Yangon',
                    'to' => 'Taunggyi',
                    'type' => 'VIP',
                    'price' => 35000,
                    'seats' => [10, 11, 12, 13, 14, 15, 16, 18, 19, 22],
                ],
                [
                    'id' => 3,
                    'company' => 'Shwe Mandalar',
                    'from' => 'Bagan',
                    'to' => 'Yangon',
                    'type' => 'Normal',
                    'price' => 27000,
                    'seats' => range(1, 21),
                ],
                [
                    'id' => 4,
                    'company' => 'OK Express',
                    'from' => 'Naypyidaw',
                    'to' => 'Mandalay',
                    'type' => 'VIP',
                    'price' => 37000,
                    'seats' => [11, 12, 13, 14, 15, 16, 17, 18, 20, 22],
                ],
                [
                    'id' => 5,
                    'company' => 'JJ Express',
                    'from' => 'Pyin Oo Lwin',
                    'to' => 'Bagan',
                    'type' => 'Normal',
                    'price' => 26000,
                    'seats' => range(1, 21),
                ],
                [
                    'id' => 6,
                    'company' => 'Elite Bus',
                    'from' => 'Mandalay',
                    'to' => 'Yangon',
                    'type' => 'VIP',
                    'price' => 36000,
                    'seats' => [10, 12, 13, 14, 15, 16, 18, 19, 21, 22],
                ]
            ]
        ];
    }


    public function getBuses(Request $request)
    {
        $date = $request->query('date');
        $from = $request->query('from');
        $to = $request->query('to');
        $type = strtolower($request->query('type'));
        $maxPrice = $request->query('price');

        $buses = $this->busData[$date] ?? [];

        $filteredBuses = array_filter($buses, function ($bus) use ($from, $to, $type, $maxPrice) {
            if ($from && strtolower($bus['from']) !== strtolower($from)) return false;
            if ($to && strtolower($bus['to']) !== strtolower($to)) return false;
            if ($type && strtolower($bus['type']) !== $type) return false;
            if ($maxPrice && $bus['price'] > $maxPrice) return false;
            return true;
        });

        $result = array_map(function ($bus) {
            unset($bus['seats']);
            return $bus;
        }, $filteredBuses);

        return response()->json(array_values($result));
    }

    public function getSeats(Request $request)
    {
        $date = $request->query('date');
        $busId = $request->query('id');

        $buses = $this->busData[$date] ?? [];

        foreach ($buses as $bus) {
            if ($bus['id'] == $busId) {
                return response()->json(['availableSeats' => $bus['seats']]);
            }
        }

        return response()->json(['availableSeats' => []]);
    }


    public function reserveSeats(Request $request)
    {
        $busId = $request->input('id');
        $date = $request->input('date');
        $requestedSeats = $request->input('seat_numbers', []);

        $buses = $this->busData[$date] ?? [];

        $bus = collect($buses)->first(function ($b) use ($busId) {
            return $b['id'] == $busId;
        });

        if (!$bus) {
            return response()->json([
                'message' => "Bus not found for ID $busId on $date."
            ], 404);
        }

        $availableSeats = $bus['seats'];
        $unavailableSeats = array_diff($requestedSeats, $availableSeats);

        if (!empty($unavailableSeats)) {
            return response()->json([
                'message' => "Some seats are not available.",
                'unavailable_seats' => array_values($unavailableSeats),
                'available_seats' => array_values($availableSeats)
            ], 400);
        }

        return response()->json([
            'message' => "Reserved seats " . implode(', ', $requestedSeats) . " on $date."
        ]);
    }

}
