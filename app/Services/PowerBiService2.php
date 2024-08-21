<?php
namespace App\Services;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class PowerBIService2
{
	private string $accessToken;

    	public function __construct(?string $accessToken = null) {
    		if (empty($accessToken)) {
    			$accessToken = $this->authorize();
    		}

    		$this->accessToken = $accessToken;
    	}


    private function authorize() : string {
        $tenantId = config("powerbi.tenant_id");

        $response = Http::asForm()->post("https://login.microsoftonline.com/{$tenantId}/oauth2/token", [
            "grant_type" => "client_credentials",
            "resource" => "https://analysis.windows.net/powerbi/api",
            "client_id" => config("powerbi.client_id"),
            "client_secret" => config("powerbi.client_secret")
        ]);
        $accessToken = json_decode($response->getBody(), false)->access_token;

        if (empty($accessToken)) {
            throw new Exception("Failed to obtain a Power BI API access token.");
        }

        return $accessToken;
    }

    public function report(string $groupId, string $reportId) : object {
        $response = Http::withToken($this->accessToken)->get("https://api.powerbi.com/v1.0/myorg/groups/{$groupId}/reports/{$reportId}");
        $data = json_decode($response->getBody(), false);

        if (empty($data)) {
            throw new Exception("Failed to obtain Power BI report data.");
        }

        return $data;
    }

    public function embedToken(string $groupId, string $reportId) : object {
        $response = Http::withToken($this->accessToken)->post("https://api.powerbi.com/v1.0/myorg/groups/{$groupId}/reports/{$reportId}/GenerateToken", [
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
