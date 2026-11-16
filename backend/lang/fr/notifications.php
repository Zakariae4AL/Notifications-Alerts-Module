<?php

return [
    'types' => [
        'trip_started' => ['label' => 'Trajet démarré', 'message' => 'Le trajet :trip a démarré.'],
        'trip_delayed' => ['label' => 'Trajet en retard', 'message' => 'Le trajet :trip a un retard de :delay minutes.'],
        'trip_completed' => ['label' => 'Trajet terminé', 'message' => 'Le trajet :trip est terminé.'],
        'passenger_boarded' => ['label' => 'Passager à bord', 'message' => ':passenger est monté(e) à bord du trajet :trip.'],
        'passenger_absent' => ['label' => 'Passager absent', 'message' => ':passenger a été signalé(e) absent(e) sur le trajet :trip.'],
        'vehicle_maintenance' => ['label' => 'Maintenance véhicule', 'message' => 'Le véhicule :vehicle doit passer en maintenance le :date.'],
        'document_expiration' => ['label' => 'Expiration de document', 'message' => 'Le document « :document » expire le :date.'],
    ],
    'severity' => [
        'info' => 'Information',
        'warning' => 'Avertissement',
        'critical' => 'Critique',
    ],
    'mail' => [
        'subject' => 'Alerte : :label',
        'greeting' => 'Bonjour :name,',
        'salutation' => "L'équipe transport",
    ],
    'preferences_saved' => 'Vos préférences de notification ont été enregistrées.',
    'alert_sent' => 'Alerte envoyée à :count destinataire(s).',
];
