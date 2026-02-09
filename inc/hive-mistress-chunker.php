<?php
/**
 * Hive Mistress Lore Chunking and Upload System
 *
 * Processes lore documents into chunks, extracts metadata,
 * generates embeddings, and uploads to Supabase vector database.
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Chunk a document into overlapping segments
 *
 * Splits by paragraphs (double newlines) while maintaining
 * chunk size and overlap constraints.
 *
 * @param string $content Document content
 * @param int $chunk_size Maximum characters per chunk (default 800)
 * @param int $overlap Overlap between chunks in characters (default 150)
 * @return array Array of chunk strings
 */
function hm_chunk_document($content, $chunk_size = 800, $overlap = 150) {
    if (empty($content)) {
        return array();
    }

    // Split by double newlines (paragraph breaks)
    $paragraphs = preg_split('/\n\s*\n/', $content);
    $chunks = array();
    $current_chunk = '';

    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);

        if (empty($paragraph)) {
            continue;
        }

        // If adding this paragraph would exceed chunk size
        if (strlen($current_chunk) + strlen($paragraph) > $chunk_size) {
            // Save current chunk if not empty
            if (!empty($current_chunk)) {
                $chunks[] = trim($current_chunk);

                // Start new chunk with overlap from end of previous chunk
                if ($overlap > 0 && strlen($current_chunk) > $overlap) {
                    $current_chunk = substr($current_chunk, -$overlap) . "\n\n" . $paragraph;
                } else {
                    $current_chunk = $paragraph;
                }
            } else {
                // Single paragraph exceeds chunk size - take it anyway
                $current_chunk = $paragraph;
            }
        } else {
            // Add paragraph to current chunk
            $current_chunk .= (empty($current_chunk) ? '' : "\n\n") . $paragraph;
        }
    }

    // Add final chunk
    if (!empty($current_chunk)) {
        $chunks[] = trim($current_chunk);
    }

    return $chunks;
}

/**
 * Extract topic from document content or filename
 *
 * Looks for markdown headers or uses filename as fallback
 *
 * @param string $content Document content
 * @param string $filename Source filename
 * @return string Topic name
 */
function hm_extract_topic($content, $filename) {
    // Try to find first markdown header
    if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
        return trim($matches[1]);
    }

    // Fallback to filename without extension
    $basename = basename($filename, '.md');
    $basename = basename($basename, '.txt');

    // Convert underscores/hyphens to spaces and title case
    $topic = str_replace(array('_', '-'), ' ', $basename);
    $topic = ucwords($topic);

    return $topic;
}

/**
 * Determine chunk type based on source and content
 *
 * @param string $source Source filename
 * @param string $content Chunk content
 * @return string Chunk type classification
 */
function hm_determine_chunk_type($source, $content) {
    $source_lower = strtolower($source);
    $content_lower = strtolower($content);

    // Check source filename patterns
    if (strpos($source_lower, 'doctrine') !== false ||
        strpos($source_lower, 'philosophy') !== false ||
        strpos($source_lower, 'creed') !== false) {
        return 'mythology';
    }

    if (strpos($source_lower, 'protocol') !== false ||
        strpos($source_lower, 'manual') !== false ||
        strpos($source_lower, 'guide') !== false) {
        return 'protocol';
    }

    if (strpos($source_lower, 'procedure') !== false ||
        strpos($source_lower, 'instruction') !== false) {
        return 'procedure';
    }

    if (strpos($source_lower, 'origin') !== false ||
        strpos($source_lower, 'dossier') !== false ||
        strpos($source_lower, 'profile') !== false) {
        return 'character';
    }

    if (strpos($source_lower, 'training') !== false ||
        strpos($source_lower, 'conditioning') !== false) {
        return 'training';
    }

    if (strpos($source_lower, 'script') !== false ||
        strpos($source_lower, 'dialogue') !== false) {
        return 'script';
    }

    // Check content patterns
    if (preg_match('/frequency|metaverse|anomie|interlink/i', $content)) {
        return 'mythology';
    }

    return 'general';
}

/**
 * Extract character mentions from content
 *
 * Finds references to known characters in the lore
 *
 * @param string $content Chunk content
 * @return array Array of character names found
 */
function hm_extract_characters($content) {
    $characters = array();

    $known_characters = array(
        'drone 22', 'd001', 'visitor 6', 'g', 'doberman',
        'angel', 'hive mistress', 'pink panthers', 'user 1'
    );

    $content_lower = strtolower($content);

    foreach ($known_characters as $character) {
        if (strpos($content_lower, $character) !== false) {
            $characters[] = $character;
        }
    }

    return array_unique($characters);
}

/**
 * Upload a single chunk to Supabase
 *
 * @param string $content Chunk content
 * @param array $embedding OpenAI embedding vector
 * @param array $metadata Additional metadata (source, topic, type, characters)
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function hm_upload_chunk($content, $embedding, $metadata = array()) {
    error_log('HM RAG DEBUG: hm_upload_chunk() called');

    $supabase_url = hm_get_supabase_url();
    $supabase_key = hm_get_supabase_key();

    if (!$supabase_url || !$supabase_key) {
        error_log('HM RAG DEBUG: Supabase credentials missing');
        return new WP_Error('config_error', 'Supabase credentials not configured');
    }

    error_log('HM RAG DEBUG: Preparing embedding vector as JSON array');

    // Clean content for JSON encoding (critical for Supabase upload)
    $clean_content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
    $clean_content = mb_scrub($clean_content, 'UTF-8');

    $row_data = array(
        'content' => $clean_content,
        'embedding' => $embedding,  // Pass array directly, json_encode will handle it
        'source' => isset($metadata['source']) ? $metadata['source'] : null,
        'topic' => isset($metadata['topic']) ? $metadata['topic'] : null,
        'chunk_type' => isset($metadata['chunk_type']) ? $metadata['chunk_type'] : null,
        'characters' => isset($metadata['characters']) ? $metadata['characters'] : array(),
    );

    // Encode and verify it's valid before sending
    $json_body = json_encode($row_data);

    if ($json_body === false) {
        error_log('HM RAG DEBUG: Supabase json_encode failed, skipping chunk');
        error_log('HM RAG DEBUG: JSON error: ' . json_last_error_msg());
        return new WP_Error('encoding_error', 'Failed to encode chunk data: ' . json_last_error_msg());
    }

    error_log('HM RAG DEBUG: Sending chunk to Supabase');
    $response = wp_remote_post($supabase_url . '/rest/v1/lore_chunks', array(
        'timeout' => 30,
        'headers' => array(
            'apikey' => $supabase_key,
            'Authorization' => 'Bearer ' . $supabase_key,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ),
        'body' => $json_body,
    ));

    if (is_wp_error($response)) {
        error_log('HM RAG DEBUG: Supabase upload WP_Error: ' . $response->get_error_message());
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    error_log("HM RAG DEBUG: Supabase upload HTTP status: {$status_code}");

    if ($status_code !== 201) {
        $body = wp_remote_retrieve_body($response);
        error_log("HM RAG DEBUG: Supabase upload failed - Response: {$body}");
        return new WP_Error(
            'upload_error',
            'Supabase upload failed with status ' . $status_code . ': ' . $body
        );
    }

    error_log('HM RAG DEBUG: Chunk uploaded successfully to Supabase');
    return true;
}

/**
 * Process a single lore file end-to-end
 *
 * Reads file, chunks it, generates embeddings, uploads to Supabase
 *
 * @param string $file_path Absolute path to lore file
 * @return array Results: ['chunks_created' => N, 'chunks_uploaded' => N, 'errors' => [...]]
 */
function hm_process_lore_file($file_path) {
    $results = array(
        'chunks_created' => 0,
        'chunks_uploaded' => 0,
        'errors' => array(),
    );

    // Read file
    if (!file_exists($file_path) || !is_readable($file_path)) {
        $results['errors'][] = 'File not readable: ' . $file_path;
        return $results;
    }

    $content = file_get_contents($file_path);

    if (empty($content)) {
        $results['errors'][] = 'File is empty: ' . $file_path;
        return $results;
    }

    // Ensure content is valid UTF-8 (fixes json_encode issues with invalid characters)
    if (!mb_check_encoding($content, 'UTF-8')) {
        error_log("HM RAG DEBUG: File has invalid UTF-8, converting: " . basename($file_path));
        $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
    }

    // Additional safety: remove any remaining invalid UTF-8 sequences
    $content = mb_scrub($content, 'UTF-8');

    // Extract metadata
    $topic = hm_extract_topic($content, $file_path);

    // Chunk the document
    $chunks = hm_chunk_document($content, 1500, 200);
    $results['chunks_created'] = count($chunks);

    if (empty($chunks)) {
        $results['errors'][] = 'No chunks created from: ' . $file_path;
        return $results;
    }

    // Process each chunk
    foreach ($chunks as $chunk) {
        // Extract metadata for this chunk
        $chunk_type = hm_determine_chunk_type($file_path, $chunk);
        $characters = hm_extract_characters($chunk);

        // Get embedding
        $embedding = hm_get_embedding($chunk);

        if (!$embedding) {
            $results['errors'][] = 'Failed to get embedding for chunk from: ' . basename($file_path);
            continue;
        }

        // Upload to Supabase
        $upload_result = hm_upload_chunk($chunk, $embedding, array(
            'source' => $file_path,
            'topic' => $topic,
            'chunk_type' => $chunk_type,
            'characters' => $characters,
        ));

        if (is_wp_error($upload_result)) {
            $results['errors'][] = 'Upload error for ' . basename($file_path) . ': ' . $upload_result->get_error_message();
        } else {
            $results['chunks_uploaded']++;
        }

        // Rate limiting: 500ms pause between chunks
        usleep(500000);
    }

    return $results;
}

/**
 * Check if a file should be included in lore upload
 *
 * Filters out session logs, implementation guides, backups, etc.
 *
 * @param string $filepath Full path to file
 * @return bool True if file should be included
 */
function hm_should_include_file($filepath) {
    $basename = basename($filepath);
    $filepath_lower = strtolower($filepath);

    // Exclude patterns
    $exclude_patterns = array(
        '/session[_-]log/i',
        '/implementation/i',
        '/backup/i',
        '/v1-backup/i',
        '/\.git/',
        '/draft/i',
        '/node_modules/i',
        '/vendor/i',
        '/component/i',
        '/wordpress/i',
        '/snippet/i',
        '/setup/i',
        '/guide/i',  // Too broad - catches component guides, setup guides
        '/rag-/i',   // RAG system docs
        '/gtm-/i',   // Google Tag Manager docs
        '/development/i',
        '/technical/i',
    );

    foreach ($exclude_patterns as $pattern) {
        if (preg_match($pattern, $filepath)) {
            return false;
        }
    }

    // Include patterns (worldbuilding keywords ONLY)
    $include_keywords = array(
        'lore', 'protocol', 'doctrine', 'field-manual', 'chain-of-command',
        'worldbuilding', 'mythology', 'drone', 'pony',
        'pink', 'panthers', 'hive', 'mistress', 'anomie',
        'dossier', 'creed', 'philosophy', 'training',
        'doberman', 'angel', 'visitor', 'error', 'frequency',
        'exodus', 'metaverse', 'lesson', 'stash', 'presider',
        'd001', 'd-001'  // Drone designation format
    );

    foreach ($include_keywords as $keyword) {
        if (strpos($filepath_lower, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

/**
 * Deduplicate lore files based on modification time
 *
 * Latest file wins conflicts. If timestamps equal, prefer /Users/ja/g/ as canonical.
 *
 * @param array $file_list Array of file paths
 * @return array Deduplicated file paths
 */
function hm_deduplicate_lore_files($file_list) {
    $deduped = array();

    foreach ($file_list as $file) {
        $basename = basename($file);

        if (!isset($deduped[$basename])) {
            $deduped[$basename] = $file;
        } else {
            // Compare modification times
            $existing_mtime = filemtime($deduped[$basename]);
            $new_mtime = filemtime($file);

            if ($new_mtime > $existing_mtime) {
                // Newer file wins
                $deduped[$basename] = $file;
            } elseif ($new_mtime === $existing_mtime) {
                // Prefer /Users/ja/g/ as canonical source
                if (strpos($file, '/Users/ja/g/') !== false) {
                    $deduped[$basename] = $file;
                }
            }
            // If existing file is newer, keep it
        }
    }

    return array_values($deduped);
}

/**
 * Scan all lore directories and find files to upload
 *
 * Scans multiple locations, filters, and deduplicates
 *
 * @return array Array of file paths to process
 */
function hm_scan_lore_directories() {
    $lore_files = array();

    // 1. WordPress theme /lore/ directory (if exists)
    // NOTE: Theme /docs/ is for development documentation, not lore
    $theme_lore = get_stylesheet_directory() . '/lore/';
    if (is_dir($theme_lore)) {
        $theme_lore_files = glob($theme_lore . '*.{md,txt}', GLOB_BRACE);
        if (is_array($theme_lore_files)) {
            $lore_files = array_merge($lore_files, $theme_lore_files);
        }
    }

    // 2. Master repository /Users/ja/g/protocols/
    $g_protocols = '/Users/ja/g/protocols/';
    if (is_dir($g_protocols)) {
        $g_protocol_files = glob($g_protocols . '*.{md,txt}', GLOB_BRACE);
        if (is_array($g_protocol_files)) {
            $lore_files = array_merge($lore_files, $g_protocol_files);
        }
    }

    // 3. Master repository /Users/ja/g/worldbuilding/
    $g_worldbuilding = '/Users/ja/g/worldbuilding/';
    if (is_dir($g_worldbuilding)) {
        $g_worldbuilding_files = glob($g_worldbuilding . '*.{md,txt}', GLOB_BRACE);
        if (is_array($g_worldbuilding_files)) {
            $lore_files = array_merge($lore_files, $g_worldbuilding_files);
        }
    }

    // 4. Unmask project directory (lore only, filtered)
    $unmask_docs = '/Users/ja/Documents/Projects/unmask/';
    if (is_dir($unmask_docs)) {
        // Scan recursively for .md and .txt files
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($unmask_docs, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match('/\.(md|txt)$/i', $file->getFilename())) {
                $lore_files[] = $file->getPathname();
            }
        }
    }

    // Filter files
    $lore_files = array_filter($lore_files, 'hm_should_include_file');

    // Deduplicate (latest file wins)
    $lore_files = hm_deduplicate_lore_files($lore_files);

    return $lore_files;
}

/**
 * Clear all lore chunks from Supabase
 *
 * Deletes all rows from lore_chunks table
 *
 * @return bool|WP_Error True on success, WP_Error on failure
 */
function hm_clear_lore_chunks() {
    $supabase_url = hm_get_supabase_url();
    $supabase_key = hm_get_supabase_key();

    if (!$supabase_url || !$supabase_key) {
        return new WP_Error('config_error', 'Supabase credentials not configured');
    }

    error_log('HM RAG DEBUG: Starting clear all chunks operation');

    // Delete all rows (Supabase allows DELETE without WHERE if you're admin)
    $response = wp_remote_request($supabase_url . '/rest/v1/lore_chunks?id=gte.0', array(
        'method' => 'DELETE',
        'timeout' => 60,  // Increased from 30 to handle large deletes
        'headers' => array(
            'apikey' => $supabase_key,
            'Authorization' => 'Bearer ' . $supabase_key,
        ),
    ));

    if (is_wp_error($response)) {
        error_log('HM RAG DEBUG: Clear chunks WP_Error: ' . $response->get_error_message());
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    error_log("HM RAG DEBUG: Clear chunks HTTP status: {$status_code}");

    if ($status_code < 200 || $status_code >= 300) {
        $body = wp_remote_retrieve_body($response);
        error_log("HM RAG DEBUG: Clear chunks failed - Response: {$body}");
        return new WP_Error(
            'delete_error',
            'Supabase delete failed with status ' . $status_code
        );
    }

    error_log('HM RAG DEBUG: Successfully cleared all chunks');
    return true;
}

/**
 * Get total count of lore chunks in Supabase
 *
 * @return int Chunk count or 0 on failure
 */
function hm_get_chunk_count() {
    $supabase_url = hm_get_supabase_url();
    $supabase_key = hm_get_supabase_key();

    if (!$supabase_url || !$supabase_key) {
        return 0;
    }

    $response = wp_remote_get($supabase_url . '/rest/v1/lore_chunks?select=count', array(
        'timeout' => 30,
        'headers' => array(
            'apikey' => $supabase_key,
            'Authorization' => 'Bearer ' . $supabase_key,
            'Prefer' => 'count=exact',
        ),
    ));

    if (is_wp_error($response)) {
        error_log('HM RAG: Failed to get chunk count: ' . $response->get_error_message());
        return 0;
    }

    // Count is in Content-Range header: "0-9/10" format
    $headers = wp_remote_retrieve_headers($response);
    $content_range = isset($headers['content-range']) ? $headers['content-range'] : '';

    if (preg_match('/\/(\d+)$/', $content_range, $matches)) {
        return intval($matches[1]);
    }

    return 0;
}
