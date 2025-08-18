<?php
// API Keys Configuration
// Store your API keys here for various services

// Sentiment Analysis APIs
$api_keys = [
    // OpenAI API (for GPT-based sentiment analysis)
    'openai' => [
        'api_key' => '', // Add your OpenAI API key here
        'base_url' => 'https://api.openai.com/v1',
        'model' => 'gpt-3.5-turbo'
    ],

    // Hugging Face API (for free sentiment analysis)
    'huggingface' => [
        'api_key' => '', // Add your Hugging Face API key here
        'base_url' => 'https://api-inference.huggingface.co/models',
        'model' => 'cardiffnlp/twitter-roberta-base-sentiment-latest'
    ],

    // VADER Sentiment (local implementation - no API key needed)
    'vader' => [
        'enabled' => true,
        'local' => true
    ],

    // TextBlob (local implementation - no API key needed)
    'textblob' => [
        'enabled' => true,
        'local' => true
    ],

    // IBM Watson Natural Language Understanding
    'watson' => [
        'api_key' => '', // Add your IBM Watson API key here
        'url' => '', // Add your IBM Watson URL here
        'version' => '2022-04-07'
    ],

    // Google Cloud Natural Language API
    'google' => [
        'api_key' => '', // Add your Google Cloud API key here
        'base_url' => 'https://language.googleapis.com/v1'
    ],

    // Azure Text Analytics
    'azure' => [
        'api_key' => '', // Add your Azure API key here
        'endpoint' => '', // Add your Azure endpoint here
        'region' => ''
    ]
];

// Default sentiment analysis method (fallback order)
$sentiment_methods = [
    'primary' => 'vader', // Use VADER as primary (local, no API key needed)
    'fallback' => ['textblob', 'huggingface', 'openai'],
    'enabled_methods' => ['vader', 'textblob'] // Only enable local methods by default
];

// Function to get API key
function get_api_key($service) {
    global $api_keys;
    return $api_keys[$service]['api_key'] ?? '';
}

// Function to check if service is enabled
function is_service_enabled($service) {
    global $api_keys, $sentiment_methods;

    if ($service === 'vader' || $service === 'textblob') {
        return $api_keys[$service]['enabled'] ?? false;
    }

    return !empty($api_keys[$service]['api_key']);
}

// Function to get enabled sentiment methods
function get_enabled_sentiment_methods() {
    global $sentiment_methods;
    return $sentiment_methods['enabled_methods'];
}
?>