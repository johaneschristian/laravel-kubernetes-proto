<?php

namespace App\ScheduledTasks;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;


class ScaleUpTask
{
    private $URL = 'http://localhost:8081/apis/apps/v1/namespaces/default/deployments/deployment-through-api';
    private $replicas = 5;

    /**
     * @throws GuzzleException
     */
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
                    'content-type' => 'application/strategic-merge-patch+json',
                ],
                'body' => $replica_data
            ]
        );
    }
}
