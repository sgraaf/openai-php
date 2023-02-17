<?php

namespace OpenAI;

class OpenAI_API_Client
{
    public const VERSION = '0.1.1';
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
        $data = [
            'model' => $model,
        ];
        if (!is_null($prompt)) {
            $data['prompt'] = $prompt;
        }
        if (!is_null($suffix)) {
            $data['suffix'] = $suffix;
        }
        if (!is_null($max_tokens)) {
            $data['max_tokens'] = $max_tokens;
        }
        if (!is_null($temperature)) {
            $data['temperature'] = $temperature;
        }
        if (!is_null($top_p)) {
            $data['top_p'] = $top_p;
        }
        if (!is_null($n)) {
            $data['n'] = $n;
        }
        if (!is_null($stream)) {
            $data['stream'] = $stream;
        }
        if (!is_null($logprobs)) {
            $data['logprobs'] = $logprobs;
        }
        if (!is_null($echo)) {
            $data['echo'] = $echo;
        }
        if (!is_null($stop)) {
            $data['stop'] = $stop;
        }
        if (!is_null($presence_penalty)) {
            $data['presence_penalty'] = $presence_penalty;
        }
        if (!is_null($frequency_penalty)) {
            $data['frequency_penalty'] = $frequency_penalty;
        }
        if (!is_null($best_of)) {
            $data['best_of'] = $best_of;
        }
        if (!is_null($logit_bias)) {
            $data['logit_bias'] = $logit_bias;
        }
        if (!is_null($user)) {
            $data['user'] = $user;
        }

        return $this->request('POST', '/completions', data: $data);
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
        $data = [
            'model' => $model,
            'instruction' => $instruction,
        ];
        if (!is_null($input)) {
            $data['input'] = $input;
        }
        if (!is_null($n)) {
            $data['n'] = $n;
        }
        if (!is_null($temperature)) {
            $data['temperature'] = $temperature;
        }
        if (!is_null($top_p)) {
            $data['top_p'] = $top_p;
        }

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
        $data = [
            'prompt' => $prompt,
        ];
        if (!is_null($n)) {
            $data['n'] = $n;
        }
        if (!is_null($size)) {
            $data['size'] = $size;
        }
        if (!is_null($response_format)) {
            $data['response_format'] = $response_format;
        }
        if (!is_null($user)) {
            $data['user'] = $user;
        }

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
        // construct the data array
        $data = [
            'image' => curl_file_create($image),
            'prompt' => $prompt,
        ];
        if (!is_null($mask)) {
            $data['mask'] = curl_file_create($mask);
        }
        if (!is_null($n)) {
            $data['n'] = $n;
        }
        if (!is_null($size)) {
            $data['size'] = $size;
        }
        if (!is_null($response_format)) {
            $data['response_format'] = $response_format;
        }
        if (!is_null($user)) {
            $data['user'] = $user;
        }

        return $this->request('POST', '/images/edits', data: $data);
    }

    public function createImageVariation(
        string $image,
        int $n = null,
        string $size = null,
        string $response_format = null,
        string $user = null,
    ): array {
        // construct the data array
        $data = [
            'image' => curl_file_create($image),
        ];
        if (!is_null($n)) {
            $data['n'] = $n;
        }
        if (!is_null($size)) {
            $data['size'] = $size;
        }
        if (!is_null($response_format)) {
            $data['response_format'] = $response_format;
        }
        if (!is_null($user)) {
            $data['user'] = $user;
        }

        return $this->request('POST', '/images/variations', data: $data);
    }

    public function createEmbeddings(
        string $model,
        string $input,
        string $user = null,
    ): array {
        // construct the data array
        $data = [
            'model' => $model,
            'input' => $input,
        ];
        if (!is_null($user)) {
            $data['user'] = $user;
        }

        return $this->request('POST', '/embeddings', data: $data);
    }

    public function listFiles(): array
    {
        return $this->request('GET', '/files');
    }

    public function uploadFile(
        string $file,
        string $purpose,
    ): array {
        // construct the data array
        $data = [
            'file' => curl_file_create($file),
            'purpose' => $purpose,
        ];

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
        $data = [
            'training_file' => $training_file,
        ];
        if (!is_null($validation_file)) {
            $data['validation_file'] = $validation_file;
        }
        if (!is_null($model)) {
            $data['model'] = $model;
        }
        if (!is_null($n_epochs)) {
            $data['n_epochs'] = $n_epochs;
        }
        if (!is_null($batch_size)) {
            $data['batch_size'] = $batch_size;
        }
        if (!is_null($learning_rate_multiplier)) {
            $data['learning_rate_multiplier'] = $learning_rate_multiplier;
        }
        if (!is_null($prompt_loss_weight)) {
            $data['prompt_loss_weight'] = $prompt_loss_weight;
        }
        if (!is_null($compute_classification_metrics)) {
            $data['compute_classification_metrics'] = $compute_classification_metrics;
        }
        if (!is_null($classification_n_classes)) {
            $data['classification_n_classes'] = $classification_n_classes;
        }
        if (!is_null($classification_positive_class)) {
            $data['classification_positive_class'] = $classification_positive_class;
        }
        if (!is_null($classification_betas)) {
            $data['classification_betas'] = $classification_betas;
        }
        if (!is_null($suffix)) {
            $data['suffix'] = $suffix;
        }

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
        $data = [
            'input' => $input,
        ];
        if (!is_null($model)) {
            $data['model'] = $model;
        }

        return $this->request('POST', '/moderations', data: $data);
    }
}
