<?php

namespace App\ScheduledTasks;

use GuzzleHttp\Client;


class ScaleDownTask
{
    private $URL = 'http://localhost:8081/apis/apps/v1/namespaces/default/deployments/deployment-through-api';
    private $replicas = 2;

    public function __invoke(): void
    {
        $replica_data = json_encode([
            'spec' => [
                'replicas' => $this->replicas
            ]
        ]);

        $client = new Client();
        $client->patch(
            $this->URL,
            [
                'headers' => [
                    'content-type' => 'application/strategic-merge-patch+json'
                ],
                'body' => $replica_data
            ]
        );
    }
}
