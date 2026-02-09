<?php
/**
 * Hive Mistress RAG (Retrieval Augmented Generation) System
 *
 * Core functionality for semantic lore search using OpenAI embeddings
 * and Supabase pgvector database.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get Supabase URL from configuration
 *
 * @return string|null Supabase URL or null if not configured
 */
function hm_get_supabase_url() {
    // Try wp-config.php constant first (recommended)
    if (defined('HM_SUPABASE_URL')) {
        return HM_SUPABASE_URL;
    }

    // Fallback to WordPress option
    $url = get_option('hm_supabase_url', '');
    return !empty($url) ? $url : null;
}

/**
 * Get Supabase API key from configuration
 *
 * @return string|null Supabase key or null if not configured
 */
function hm_get_supabase_key() {
    // Try wp-config.php constant first (recommended)
    if (defined('HM_SUPABASE_KEY')) {
        return HM_SUPABASE_KEY;
    }

    // Fallback to WordPress option
    $key = get_option('hm_supabase_key', '');
    return !empty($key) ? $key : null;
}

/**
 * Get OpenAI API key from configuration
 *
 * @return string|null OpenAI key or null if not configured
 */
function hm_get_openai_key() {
    // Try wp-config.php constant first (recommended)
    if (defined('HM_OPENAI_API_KEY')) {
        return HM_OPENAI_API_KEY;
    }

    // Fallback to WordPress option
    $key = get_option('hm_openai_api_key', '');
    return !empty($key) ? $key : null;
}

/**
 * Get embedding vector from OpenAI for given text
 *
 * Uses text-embedding-3-small model (1536 dimensions)
 *
 * @param string $text Text to embed
 * @return array|null Embedding vector or null on failure
 */
function hm_get_embedding($text) {
    $api_key = hm_get_openai_key();

    if (!$api_key) {
        error_log('HM RAG DEBUG: OpenAI API key not configured');
        return null;
    }

    if (empty($text)) {
        error_log('HM RAG DEBUG: Empty text provided for embedding');
        return null;
    }

    // Debug: Show API key is being used (first/last 4 chars only)
    $key_preview = substr($api_key, 0, 7) . '...' . substr($api_key, -4);
    error_log("HM RAG DEBUG: Using OpenAI API key: {$key_preview}");
    error_log("HM RAG DEBUG: Text length: " . strlen($text) . " characters");

    // Clean text to ensure valid UTF-8 for json_encode
    $clean_text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
    $clean_text = mb_scrub($clean_text, 'UTF-8');

    $request_body = array(
        'input' => $clean_text,
        'model' => 'text-embedding-3-small',
    );

    // Encode and verify it's valid
    $json_body = json_encode($request_body);

    if ($json_body === false) {
        error_log('HM RAG DEBUG: json_encode failed (invalid UTF-8), skipping this chunk');
        error_log('HM RAG DEBUG: JSON error: ' . json_last_error_msg());
        return null;
    }

    error_log("HM RAG DEBUG: Sending request to OpenAI embeddings API");

    $response = wp_remote_post('https://api.openai.com/v1/embeddings', array(
        'timeout' => 30,
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => $json_body,
    ));

    if (is_wp_error($response)) {
        error_log('HM RAG DEBUG: OpenAI API WP_Error: ' . $response->get_error_message());
        error_log('HM RAG DEBUG: WP_Error code: ' . $response->get_error_code());
        return null;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    error_log("HM RAG DEBUG: OpenAI API HTTP status: {$status_code}");
    error_log("HM RAG DEBUG: OpenAI API response body: " . substr($response_body, 0, 500));

    $body = json_decode($response_body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('HM RAG DEBUG: JSON decode error: ' . json_last_error_msg());
        return null;
    }

    if (!isset($body['data'][0]['embedding'])) {
        error_log('HM RAG DEBUG: Invalid OpenAI response structure');
        error_log('HM RAG DEBUG: Full response: ' . print_r($body, true));
        return null;
    }

    error_log('HM RAG DEBUG: Successfully got embedding (' . count($body['data'][0]['embedding']) . ' dimensions)');
    return $body['data'][0]['embedding'];
}

/**
 * Search Supabase lore_chunks table for similar content
 *
 * Calls match_lore_chunks RPC function with cosine similarity
 *
 * @param array $embedding Query embedding vector (1536 dimensions)
 * @param int $match_count Number of results to return (default 3)
 * @param float $threshold Minimum similarity threshold 0-1 (default 0.7)
 * @return array Array of matching chunks or empty array on failure
 */
function hm_search_lore($embedding, $match_count = 3, $threshold = 0.7) {
    $supabase_url = hm_get_supabase_url();
    $supabase_key = hm_get_supabase_key();

    if (!$supabase_url || !$supabase_key) {
        error_log('HM RAG: Supabase credentials not configured');
        return array();
    }

    if (!is_array($embedding) || count($embedding) !== 1536) {
        error_log('HM RAG: Invalid embedding vector provided');
        return array();
    }

    error_log("HM RAG DEBUG: Searching Supabase with match_count={$match_count}, threshold={$threshold}");
    error_log("HM RAG DEBUG: Supabase URL: {$supabase_url}/rest/v1/rpc/match_lore_chunks");

    $response = wp_remote_post($supabase_url . '/rest/v1/rpc/match_lore_chunks', array(
        'timeout' => 30,
        'headers' => array(
            'apikey' => $supabase_key,
            'Authorization' => 'Bearer ' . $supabase_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'query_embedding' => $embedding,  // Pass array directly, json_encode will handle it
            'match_count' => $match_count,
            'match_threshold' => $threshold,
        )),
    ));

    if (is_wp_error($response)) {
        error_log('HM RAG DEBUG: Supabase API WP_Error: ' . $response->get_error_message());
        return array();
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    error_log("HM RAG DEBUG: Supabase returned status {$status_code}");
    error_log("HM RAG DEBUG: Supabase response: " . substr($response_body, 0, 500));

    if ($status_code !== 200) {
        error_log('HM RAG DEBUG: Supabase error - Status ' . $status_code . ': ' . $response_body);
        return array();
    }

    $chunks = json_decode($response_body, true);

    if (!is_array($chunks)) {
        error_log('HM RAG DEBUG: Invalid Supabase response - not an array');
        error_log('HM RAG DEBUG: Response type: ' . gettype($chunks));
        return array();
    }

    error_log('HM RAG DEBUG: Found ' . count($chunks) . ' matching chunks');

    // Log similarity scores for debugging
    if (!empty($chunks)) {
        foreach ($chunks as $i => $chunk) {
            $similarity = isset($chunk['similarity']) ? round($chunk['similarity'] * 100, 1) : 'N/A';
            $source = isset($chunk['source']) ? basename($chunk['source']) : 'unknown';
            $topic = isset($chunk['topic']) ? $chunk['topic'] : 'N/A';
            error_log("HM RAG DEBUG: Chunk " . ($i + 1) . ": {$similarity}% similarity | {$source} | Topic: {$topic}");
        }
    }

    return $chunks;
}

/**
 * Get relevant lore for a user message
 *
 * Main entry point for RAG system. Returns formatted lore context
 * that can be injected into the AI prompt.
 *
 * @param string $user_message User's message text
 * @param int $match_count Number of lore chunks to retrieve (default 3)
 * @return string Formatted lore context or empty string on failure
 */
function hm_get_relevant_lore($user_message, $match_count = 3) {
    if (empty($user_message)) {
        return '';
    }

    // Get embedding for user message
    $embedding = hm_get_embedding($user_message);

    if (!$embedding) {
        // Graceful degradation: return empty string, don't break chat
        return '';
    }

    // Search for similar lore chunks (0.65 threshold to avoid irrelevant results)
    $chunks = hm_search_lore($embedding, 12, 0.65);

    if (empty($chunks)) {
        return '';
    }

    // Filter to only high-relevance chunks and limit to top 3
    $filtered_chunks = array_filter($chunks, function($chunk) {
        return isset($chunk['similarity']) && $chunk['similarity'] >= 0.65;
    });

    $filtered_chunks = array_slice($filtered_chunks, 0, $match_count);

    if (empty($filtered_chunks)) {
        return '';
    }

    // Format chunks for prompt injection
    $lore_context = "---\n\nRELEVANT LORE (use this to enrich your response):\n\n";

    foreach ($filtered_chunks as $i => $chunk) {
        $num = $i + 1;
        $lore_context .= "**Lore Fragment {$num}";

        if (!empty($chunk['topic'])) {
            $lore_context .= " - {$chunk['topic']}";
        }

        $lore_context .= "**\n";
        $lore_context .= $chunk['content'] . "\n";

        if (!empty($chunk['source'])) {
            $lore_context .= "_Source: " . basename($chunk['source']) . "_\n";
        }

        $lore_context .= "\n";
    }

    $lore_context .= "Reference this lore ONLY if directly relevant to the user's question. Avoid forcing references.\n\n---\n";

    return $lore_context;
}

/**
 * Check RAG system configuration status
 *
 * @return array Status information for admin display
 */
function hm_rag_status() {
    $status = array(
        'supabase_url' => hm_get_supabase_url() ? 'Set' : 'Not Set',
        'supabase_key' => hm_get_supabase_key() ? 'Set' : 'Not Set',
        'openai_key' => hm_get_openai_key() ? 'Set' : 'Not Set',
        'configured' => false,
        'chunk_count' => 0,
    );

    $status['configured'] = (
        $status['supabase_url'] === 'Set' &&
        $status['supabase_key'] === 'Set' &&
        $status['openai_key'] === 'Set'
    );

    // Get chunk count from Supabase if configured
    if ($status['configured'] && function_exists('hm_get_chunk_count')) {
        $status['chunk_count'] = hm_get_chunk_count();
    }

    return $status;
}
