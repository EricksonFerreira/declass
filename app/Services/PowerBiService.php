<?php
namespace App\Services;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class PowerBIService
{
    protected $client;
    protected $clientId;
    protected $clientSecret;
    protected $tenantId;

    public function __construct()
    {
        $this->client = new Client();
        $this->clientId = env('POWERBI_CLIENT_ID');
        $this->clientSecret = env('POWERBI_CLIENT_SECRET');
        $this->tenantId = env('POWERBI_TENANT_ID');
    }

    public function getAccessToken()
    {
        $this->criarTabelaTokenCasoNecessario();

        $token = DB::table('powerbi_tokens')->latest('expires_at')->first();

        if ($token && Carbon::parse($token->expires_at)->isFuture()) {
            return $token->access_token;
        }

        $newTokenData = $this->requestNewToken();

        DB::table('powerbi_tokens')->insert([
            'access_token' => $newTokenData['access_token'],
            'expires_at' => Carbon::now()->addSeconds($newTokenData['expires_in']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $newTokenData['access_token'];
    }

    private function requestNewToken()
    {
        $response = $this->client->post("https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token", [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://analysis.windows.net/powerbi/api/.default'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function listReports($groupId)
    {
        $accessToken = $this->getAccessToken();

        $response = $this->client->get("https://api.powerbi.com/v1.0/myorg/groups/{$groupId}/reports", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}"
            ]
        ]);

        return json_decode($response->getBody(), true);
    }


    public function listGroups()
    {
        $accessToken = $this->getAccessToken();
        $response = $this->client->get('https://api.powerbi.com/v1.0/myorg/groups', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
    private function criarTabelaTokenCasoNecessario()
    {
        if (!SchemaCache::hasTable('powerbi_tokens')) {
            Schema::create('powerbi_tokens', function (Blueprint $table) {
                $table->id();
                $table->text('access_token');
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }
    }

    public function embedToken(string $groupId, string $reportId) : object {
        $accessToken = $this->getAccessToken();
        $response = Http::withToken($accessToken)->post("https://api.powerbi.com/v1.0/myorg/groups/{$groupId}/reports/{$reportId}/GenerateToken", [
            "dataset" => [],
            "reports" => [["id" => $reportId]]
        ]);
        $data = json_decode($response->getBody(), false);

        if (empty($data)) {
            throw new Exception("Failed to obtain Power BI report embed token.");
        }

        return $data;
    }

}
