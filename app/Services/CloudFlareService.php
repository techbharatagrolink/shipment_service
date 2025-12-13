<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class CloudFlareService
{
    protected S3Client $client;
    protected string $bucket;

    public function __construct()
    {
        $this->bucket = env('CLOUDFLARE_R2_BUCKET');

        $this->client = new S3Client([
            'version'     => 'latest',
            'region'      => 'auto',
            'endpoint'    => 'https://' . env('CLOUDFLARE_ACCOUNT_ID') . '.r2.cloudflarestorage.com',
            'credentials' => [
                'key'    => env('CLOUDFLARE_R2_ACCESS_KEY'),
                'secret' => env('CLOUDFLARE_R2_SECRET_KEY'),
            ],
        ]);
    }

    /**
     * Upload file to Cloudflare R2
     */
    public function upload(
        string $filePath,
        string $r2Path,
        string $contentType = 'application/pdf',
        bool $public = true
    ): string {
        try {
            $res = $this->client->putObject([
                'Bucket'      => $this->bucket,
                'Key'         => $r2Path,
                'SourceFile'  => $filePath,
                'ContentType'=> $contentType,
                'ACL'         => $public ? 'public-read' : 'private',
            ]);

            //dd($res,$r2Path);

            return $this->getPublicUrl($r2Path);

        } catch (AwsException $e) {
            throw new \Exception('R2 Upload Failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate public URL
     */
    public function getPublicUrl(string $path): string
    {
        return env('CLOUDFLARE_R2_PUBLIC_URL').$path;
    }
}
