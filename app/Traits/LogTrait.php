<?php

namespace App\Traits;

use DB;

trait LogTrait
{
    private function adicionaJobLog($job_id){
        DB::table('log_jobs')->insert([
            'job_id' =>$job_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
