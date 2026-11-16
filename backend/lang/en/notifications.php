<?php

return [
    'types' => [
        'trip_started' => ['label' => 'Trip started', 'message' => 'Trip :trip has started.'],
        'trip_delayed' => ['label' => 'Trip delayed', 'message' => 'Trip :trip is delayed by :delay minutes.'],
        'trip_completed' => ['label' => 'Trip completed', 'message' => 'Trip :trip has been completed.'],
        'passenger_boarded' => ['label' => 'Passenger boarded', 'message' => ':passenger has boarded trip :trip.'],
        'passenger_absent' => ['label' => 'Passenger absent', 'message' => ':passenger was marked absent on trip :trip.'],
        'vehicle_maintenance' => ['label' => 'Vehicle maintenance', 'message' => 'Vehicle :vehicle is due for maintenance on :date.'],
        'document_expiration' => ['label' => 'Document expiration', 'message' => 'Document ":document" expires on :date.'],
    ],
    'severity' => [
        'info' => 'Information',
        'warning' => 'Warning',
        'critical' => 'Critical',
    ],
    'mail' => [
        'subject' => 'Alert: :label',
        'greeting' => 'Hello :name,',
        'salutation' => 'The transport team',
    ],
    'preferences_saved' => 'Your notification preferences have been saved.',
    'alert_sent' => 'Alert sent to :count recipient(s).',
];
