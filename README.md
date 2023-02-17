# OpenAI PHP

[![Packagist Version](https://img.shields.io/packagist/v/sgraaf/openai-php)](https://packagist.org/packages/sgraaf/openai-php)
[![Packagist PHP Version](https://img.shields.io/packagist/dependency-v/sgraaf/openai-php/php)](https://img.shields.io/packagist/dependency-v/sgraaf/openai-php/php)
[![pre-commit.ci status](https://results.pre-commit.ci/badge/github/sgraaf/openai-php/main.svg)](https://results.pre-commit.ci/latest/github/sgraaf/openai-php/main)
[![Packagist License](https://img.shields.io/packagist/l/sgraaf/openai-php)](./LICENSE)

A thin PHP wrapper for the OpenAI API built upon `libcurl`.

## Installation

You can install OpenAI PHP via [Composer](https://getcomposer.org/):

```bash
composer require sgraaf/openai-php
```

## Usage

### Models

#### List models

Lists the currently available models, and provides basic information about each one such as the owner and availability. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/models/list) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// lists the currently available models
var_dump($client->listModels());
```

#### Retrieve model

Retrieves a model instance, providing basic information about the model such as the owner and permissioning. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/models/retrieve) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// retrieves a model instance
var_dump($client->retrieveModel(model: 'text-davinci-003'));
```

### Completions

#### Create completion

Creates a completion for the provided prompt and parameters. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/completions/create) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// creates a completion for the provided prompt and parameters
var_dump($client->createCompletion(model: 'text-davinci-003', prompt: 'Say this is a test', max_tokens: 7, temperature: 0));
```

### Edits

#### Create edit

Creates a new edit for the provided input, instruction, and parameters. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/edits/create) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// creates a new edit for the provided input, instruction, and parameters
var_dump($client->createEdit(model: 'text-davinci-003', input: 'What day of the wek is it?', instruction: 'Fix the spelling mistakes'));
```

### Images

#### Create image

Creates an image given a prompt. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/images/create) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// creates an image given a prompt
var_dump($client->createImage(prompt: 'A cute baby sea otter', n: 2, size: '1024x1024'));
```

#### Create image edit

Creates an edited or extended image given an original image and a prompt. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/images/create-edit) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// creates an edited or extended image given an original image and a prompt
var_dump($client->createImageEdit(image: 'otter.png', mask: 'mask.png', prompt: 'A cute baby sea otter wearing a beret', n: 2, size: '1024x1024'));
```

#### Create image variation

Creates a variation of a given image. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/images/create-variation) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// creates a variation of a given image
var_dump($client->createImageVariation(image: 'otter.png', n: 2, size: '1024x1024'));
```

### Embeddings

#### Create embeddings

Creates an embedding vector representing the input text. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/embeddings/create) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// creates an embedding vector representing the input text
var_dump($client->createEmbedding(model: 'text-embedding-ada-002', input: 'The food was delicious and the waiter...'));
```

### Files

#### List files

Returns a list of files that belong to the user's organization. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/files/list) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// returns a list of files that belong to the user's organization
var_dump($client->listFiles());
```

#### Upload file

Upload a file that contains document(s) to be used across various endpoints/features. Currently, the size of all the files uploaded by one organization can be up to 1 GB. Please contact OpenAI if you need to increase the storage limit. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/files/upload) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// upload a file that contains document(s) to be used across various endpoints/features
var_dump($client->createFile(file: 'mydata.jsonl', purpose: 'fine-tune'));
```

#### Delete file

Delete a file. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/files/delete) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// delete a file
var_dump($client->deleteFile(file_id: 'file-XjGxS3KTG0uNmNOK362iJua3'));
```

#### Retrieve file

Returns information about a specific file. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/files/retrieve) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// returns information about a specific file
var_dump($client->retrieveFile(file_id: 'file-XjGxS3KTG0uNmNOK362iJua3'));
```

#### Retrieve file content

Returns the contents of the specified file. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/files/retrieve-content) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// returns the contents of the specified file
var_dump($client->downloadFile(file_id: 'file-XjGxS3KTG0uNmNOK362iJua3'));
```

### Fine-Tunes

#### Create fine-tune

Creates a job that fine-tunes a specified model from a given dataset.

Response includes details of the enqueued job including job status and the name of the fine-tuned models once complete.

See the [OpenAI docs](https://platform.openai.com/docs/api-reference/fine-tunes/create) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// creates a job that fine-tunes a specified model from a given dataset
var_dump($client->createFineTune(training_file: 'file-XGinujblHPwGLSztz8cPS8XY'));
```

#### List fine-tunes

List your organization's fine-tuning jobs. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/fine-tunes/list) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// list your organization's fine-tuning jobs

var_dump($client->listFineTunes());
```

#### Retrieve fine-tune

Gets info about the fine-tune job. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/fine-tunes/retrieve) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// gets info about the fine-tune job
var_dump($client->retrieveFineTune(fine_tune_id: 'ft-AF1WoRqd3aJAHsqc9NY7iL8F'));
```

#### Cancel fine-tune

Immediately cancel a fine-tune job. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/fine-tunes/cancel) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// immediately cancel a fine-tune job
var_dump($client->cancelFineTune(fine_tune_id: 'ft-AF1WoRqd3aJAHsqc9NY7iL8F'));
```

#### List fine-tune events

Get fine-grained status updates for a fine-tune job. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/fine-tunes/events) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// get fine-grained status updates for a fine-tune job.

var_dump($client->listFineTuneEvents(fine_tune_id: 'ft-AF1WoRqd3aJAHsqc9NY7iL8F'));
```

#### Delete fine-tune model

Delete a fine-tuned model. You must have the Owner role in your organization. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/fine-tunes/delete-model) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// delete a fine-tuned model
var_dump($client->deleteModel(model: 'curie:ft-acmeco-2021-03-03-21-44-20'));
```

### Moderations

#### Create moderation

Classifies if text violates OpenAI's Content Policy. See the [OpenAI docs](https://platform.openai.com/docs/api-reference/moderations/create) for more information.

```php
// initialize the client
$client = new OpenAI\Client('YOUR_OPENAI_API_KEY');

// classifies if text violates openai's content policy
var_dump($client->createModeration(input: 'I want to kill them.'));
```
