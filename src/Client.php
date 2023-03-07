<?php

namespace OpenAI;

class Client
{
    public const VERSION = '0.1.5';
    public const API_URL = 'https://api.openai.com/v1';
    protected array $headers;
    protected array $curl_opts;
    protected \CurlHandle $ch;

    public function __construct(string $api_key, string $organization = null)
    {
        // construct the headers and cURL options
        $this->headers = [
            'Content-Type: application/json',
            "Authorization: Bearer {$api_key}",
        ];
        if (!empty($organization)) {
            $this->headers[] = "OpenAI-Organization: {$organization}";
        }
        $this->curl_opts = [
            CURLOPT_USERAGENT => 'openai-php/' . self::VERSION,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
        ];

        // initialize cURL handle w/ API key
        $this->ch = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }

    private function request(string $method, string $endpoint, float $timeout = 0.0, array $data = []): array
    {
        // initialize cURL options array
        $curl_opts = [
            CURLOPT_URL => self::API_URL . $endpoint,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_TIMEOUT_MS => $timeout * 1000,
        ];

        // set the request method
        switch ($method) {
            case 'POST':
                $curl_opts[CURLOPT_POST] = true;
                break;
            case 'GET':
                $curl_opts[CURLOPT_HTTPGET] = true;
                break;
            default:
                $curl_opts[CURLOPT_CUSTOMREQUEST] = $method;
                break;
        }

        // handle data
        if (!empty($data)) {
            if (array_key_exists('file', $data) || array_key_exists('image', $data)) {
                $this->headers[0] = 'Content-Type: multipart/form-data';
                $post_fields = $data;
            } else {
                $this->headers[0] = 'Content-Type: application/json';
                $post_fields = json_encode($data);
            }
            $curl_opts[CURLOPT_POSTFIELDS] = $post_fields;
        }
        $curl_opts[CURLOPT_HTTPHEADER] = $this->headers;

        // reset cURL handle option
        curl_reset($this->ch);

        // set cURL options
        curl_setopt_array($this->ch, $curl_opts + $this->curl_opts);

        $response = curl_exec($this->ch);

        // get the HTTP status code
        $http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        // throw an exception if an error has occurred
        $error_msg = null;
        if (curl_errno($this->ch)) {
            $error_msg = 'cURL error: ' . curl_error($this->ch);
        } elseif ($http_code >= 400) {
            $error_msg = "API error ({$http_code}): " . json_decode($response, true)['error']['message'];
        }

        if (!empty($error_msg)) {
            throw new \Exception($error_msg);
        }

        // return the response as an array
        return json_decode($response, true);
    }

    private function filter(mixed $value): bool {
        return !is_null($value);
    }

    public function listModels(): array
    {
        return $this->request('GET', '/models');
    }

    public function retrieveModel(string $model): array
    {
        return $this->request('GET', "/models/{$model}");
    }

    public function createCompletion(
        string $model,
        string $prompt = null,
        string $suffix = null,
        int $max_tokens = null,
        float $temperature = null,
        float $top_p = null,
        int $n = null,
        bool $stream = null,
        int $logprobs = null,
        bool $echo = null,
        mixed $stop = null,
        float $presence_penalty = null,
        float $frequency_penalty = null,
        int $best_of = null,
        array $logit_bias = null,
        string $user = null,
    ): array {
        // construct the data array
        $data = array_filter(
            compact(
                'model',
                'prompt',
                'suffix',
                'max_tokens',
                'temperature',
                'top_p',
                'n',
                'stream',
                'logprobs',
                'echo',
                'stop',
                'presence_penalty',
                'frequency_penalty',
                'best_of',
                'logit_bias',
                'user',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/completions', data: $data);
    }

    public function createChatCompletion(
        string $model,
        array $messages,
        float $temperature = null,
        float $top_p = null,
        int $n = null,
        bool $stream = null,
        mixed $stop = null,
        int $max_tokens = null,
        float $presence_penalty = null,
        float $frequency_penalty = null,
        array $logit_bias = null,
        string $user = null,
    ): array {
        // construct the data array
        $data = array_filter(
            compact(
                'model',
                'messages',
                'temperature',
                'top_p',
                'n',
                'stream',
                'stop',
                'max_tokens',
                'presence_penalty',
                'frequency_penalty',
                'logit_bias',
                'user',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/chat/completions', data: $data);
    }

    public function createEdit(
        string $model,
        string $instruction,
        string $input = null,
        int $n = null,
        float $temperature = null,
        float $top_p = null,
    ): array {
        // construct the data array
        $data = array_filter(
            compact(
                'model',
                'instruction',
                'input',
                'n',
                'temperature',
                'top_p',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/edits', data: $data);
    }

    public function createImage(
        string $prompt,
        int $n = null,
        string $size = null,
        string $response_format = null,
        string $user = null,
    ): array {
        // construct the data array
        $data = array_filter(
            compact(
                'prompt',
                'n',
                'size',
                'response_format',
                'user',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/images/generations', data: $data);
    }

    public function createImageEdit(
        string $image,
        string $prompt,
        string $mask = null,
        int $n = null,
        string $size = null,
        string $response_format = null,
        string $user = null,
    ): array {
        // convert image and possibly mask to `CURLFile` objects
        $image = new \CURLFile($image);
        if (!is_null($mask)) {
            $mask = new \CURLFile($mask);
        }

        // construct the data array
        $data = array_filter(
            compact(
                'image',
                'prompt',
                'mask',
                'n',
                'size',
                'response_format',
                'user',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/images/edits', data: $data);
    }

    public function createImageVariation(
        string $image,
        int $n = null,
        string $size = null,
        string $response_format = null,
        string $user = null,
    ): array {
        // convert image to `CURLFile` object
        $image = new \CURLFile($image);

        // construct the data array
        $data = array_filter(
            compact(
                'image',
                'n',
                'size',
                'response_format',
                'user',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/images/variations', data: $data);
    }

    public function createEmbedding(
        string $model,
        string $input,
        string $user = null,
    ): array {
        // construct the data array
        $data = array_filter(
            compact(
                'model',
                'input',
                'user',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/embeddings', data: $data);
    }

    public function createTranscription(
        string $file,
        string $model,
        string $prompt = null,
        string $response_format = null,
        float $temperature = null,
        string $language = null,
    ): array {
        // convert file to `CURLFile` object
        $file = new \CURLFile($file);

        // construct the data array
        $data = array_filter(
            compact(
                'file',
                'model',
                'prompt',
                'response_format',
                'temperature',
                'language',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/audio/transcriptions', data: $data);
    }

    public function createTranslation(
        string $file,
        string $model,
        string $prompt = null,
        string $response_format = null,
        float $temperature = null,
    ): array {
        // convert file to `CURLFile` object
        $file = new \CURLFile($file);

        // construct the data array
        $data = array_filter(
            compact(
                'file',
                'model',
                'prompt',
                'response_format',
                'temperature',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/audio/translations', data: $data);
    }

    public function listFiles(): array
    {
        return $this->request('GET', '/files');
    }

    public function uploadFile(
        string $file,
        string $purpose,
    ): array {
        // convert file to `CURLFile` object
        $file = new \CURLFile($file);

        // construct the data array
        $data = array_filter(
            compact(
                'file',
                'purpose',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/files', data: $data);
    }

    public function deleteFile(
        string $file_id,
    ): array {
        return $this->request('DELETE', "/files/{$file_id}");
    }

    public function retrieveFile(
        string $file_id,
    ): array {
        return $this->request('GET', "/files/{$file_id}");
    }

    public function retrieveFileContent(
        string $file_id,
    ): array {
        return $this->request('GET', "/files/{$file_id}/content");
    }

    public function createFineTune(
        string $training_file,
        string $validation_file = null,
        string $model = null,
        int $n_epochs = null,
        int $batch_size = null,
        float $learning_rate_multiplier = null,
        float $prompt_loss_weight = null,
        bool $compute_classification_metrics = null,
        int $classification_n_classes = null,
        string $classification_positive_class = null,
        array $classification_betas = null,
        string $suffix = null,
    ): array {
        // construct the data array
        $data = array_filter(
            compact(
                'training_file',
                'validation_file',
                'model',
                'n_epochs',
                'batch_size',
                'learning_rate_multiplier',
                'prompt_loss_weight',
                'compute_classification_metrics',
                'classification_n_classes',
                'classification_positive_class',
                'classification_betas',
                'suffix',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/fine-tunes', data: $data);
    }

    public function listFineTunes(): array
    {
        return $this->request('GET', '/fine-tunes');
    }

    public function retrieveFineTune(
        string $fine_tune_id,
    ): array {
        return $this->request('GET', "/fine-tunes/{$fine_tune_id}");
    }

    public function cancelFineTune(
        string $fine_tune_id,
    ): array {
        return $this->request('POST', "/fine-tunes/{$fine_tune_id}/cancel");
    }

    public function listFineTuneEvents(
        string $fine_tune_id,
        bool $stream = null,
    ): array {
        $endpoint = "/fine-tunes/{$fine_tune_id}/events";
        if (!is_null($stream)) {
            $endpoint .= '?' . http_build_query(['stream' => $stream]);
        }

        return $this->request('GET', $endpoint);
    }

    public function deleteFineTuneModel(
        string $model,
    ): array {
        return $this->request('DELETE', "/models/{$model}");
    }

    public function createModeration(
        mixed $input,
        string $model = null,
    ): array {
        // construct the data array
        $data = array_filter(
            compact(
                'input',
                'model',
            ),
            [$this, 'filter'],
        );

        return $this->request('POST', '/moderations', data: $data);
    }
}
