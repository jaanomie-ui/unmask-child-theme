# Hive Mistress RAG System Setup Guide

**Created:** 2026-01-17
**Version:** 1.0

This guide walks you through setting up the RAG (Retrieval Augmented Generation) system for the Hive Mistress chatbot.

---

## What is RAG?

RAG allows the Hive Mistress to dynamically pull relevant lore from 70+ worldbuilding documents based on what the pony asks about. Instead of having all lore in the system prompt, the AI searches a vector database for the most relevant fragments and weaves them into responses.

**Benefits:**
- AI responses enriched with deep mythology
- Automatic discovery of relevant lore based on questions
- Handles massive lore corpus (70+ files, hundreds of KB)
- Gracefully degrades if RAG fails (chat still works)

---

## Prerequisites

You need three services:

1. **Supabase Account** (free tier is fine)
   - Sign up at https://supabase.com
   - Create a new project
   - Note your project URL and API keys

2. **OpenAI Account**
   - Sign up at https://platform.openai.com
   - Add payment method (usage will be ~$3-4/month)
   - Generate an API key

3. **WordPress Admin Access**
   - Need to edit `wp-config.php`
   - Need to access Settings → Hive Mistress

---

## Step 1: Create Supabase Database

### 1.1 Enable pgvector Extension

1. Go to your Supabase project
2. Click **SQL Editor** in left sidebar
3. Run this SQL:

```sql
create extension if not exists vector;
```

### 1.2 Create lore_chunks Table

Run this SQL in the SQL Editor:

```sql
create table lore_chunks (
    id bigserial primary key,
    content text not null,
    embedding vector(1536),
    source text,
    topic text,
    chunk_type text,
    characters text[],
    created_at timestamptz default now()
);
```

### 1.3 Create Vector Index

Run this SQL to create a fast similarity search index:

```sql
create index lore_chunks_embedding_idx
    on lore_chunks using ivfflat (embedding vector_cosine_ops)
    with (lists = 10);
```

### 1.4 Create Search Function

This RPC function performs the similarity search:

```sql
create or replace function match_lore_chunks(
    query_embedding vector(1536),
    match_count int default 3,
    match_threshold float default 0.7
)
returns table (
    id bigint,
    content text,
    source text,
    topic text,
    chunk_type text,
    similarity float
)
language plpgsql
as $$
begin
    return query
    select
        lore_chunks.id,
        lore_chunks.content,
        lore_chunks.source,
        lore_chunks.topic,
        lore_chunks.chunk_type,
        1 - (lore_chunks.embedding <=> query_embedding) as similarity
    from lore_chunks
    where 1 - (lore_chunks.embedding <=> query_embedding) > match_threshold
    order by lore_chunks.embedding <=> query_embedding
    limit match_count;
end;
$$;
```

### 1.5 Enable Row Level Security (Optional but Recommended)

```sql
-- Enable RLS on the table
alter table lore_chunks enable row level security;

-- Allow public reads (needed for RAG search)
create policy "Allow public reads"
    on lore_chunks for select
    using (true);

-- Allow service role to insert/update/delete
create policy "Allow service role writes"
    on lore_chunks for all
    using (auth.role() = 'service_role');
```

### 1.6 Get Your Supabase Credentials

1. Go to **Settings → API** in Supabase dashboard
2. Copy your **Project URL** (e.g., `https://xxx.supabase.co`)
3. Copy your **anon public key** (for read operations)
4. Copy your **service_role key** (for write operations - keep secret!)

**Important:** For admin uploads, you'll need the service_role key. For production, you can use the anon key if you set up RLS properly.

---

## Step 2: Configure WordPress

### 2.1 Add API Keys to wp-config.php

**Recommended Method:** Add these constants to your `wp-config.php` file (above the line that says "That's all, stop editing!"):

```php
// Hive Mistress RAG Configuration
define('HM_SUPABASE_URL', 'https://your-project.supabase.co');
define('HM_SUPABASE_KEY', 'your-service-role-key-here');
define('HM_OPENAI_API_KEY', 'sk-your-openai-key-here');
```

**Why wp-config.php?**
- Keys stored outside web root (more secure)
- Not in database (won't leak via SQL dumps)
- Already gitignored
- Requires server file access to compromise

**Alternative Method:** You can also store keys in WordPress options via the admin UI (coming in a future update).

### 2.2 Verify Configuration

1. Go to **Settings → Hive Mistress** in WordPress admin
2. Click the **RAG** tab
3. Check that all three credentials show as **"Set"** (green)
4. If any show "Not Set" (red), double-check your wp-config.php

---

## Step 3: Upload Lore Files

### 3.1 Prepare Lore Directories

The system will scan these directories for .md and .txt files:

**On Server:**
- `/wp-content/themes/buddyboss-theme-child/lore/` (if exists)
- `/wp-content/themes/buddyboss-theme-child/docs/`

**On Local Computer (if running locally):**
- `/Users/ja/g/protocols/`
- `/Users/ja/g/worldbuilding/`
- `/Users/ja/Documents/Projects/unmask/`

**What Gets Included:**
Files containing keywords: lore, protocol, doctrine, field, manual, worldbuilding, mythology, drone, pony, pink, panthers, hive, mistress, anomie, doberman, angel, visitor, frequency

**What Gets Excluded:**
- Session logs
- Implementation guides
- Backup files
- Drafts
- Git files

### 3.2 Upload Process

1. Go to **Settings → Hive Mistress → RAG** in WordPress admin
2. Click **"Upload Lore Files"**
3. Confirm the upload (this will take 5-10 minutes for 70+ files)
4. Wait for completion message

**What Happens:**
- System scans all configured directories
- Deduplicates files (latest modification time wins)
- Chunks each file into 1500-character segments with 200-char overlap
- Extracts metadata (topic, type, characters)
- Gets OpenAI embedding for each chunk (costs ~$3.50 one-time)
- Uploads to Supabase with vector embedding
- Rate limits at 500ms between chunks to avoid API throttling

**Expected Results:**
- 70+ files processed
- ~1000 chunks created
- ~1000 chunks uploaded
- Chunk count in dashboard updates

### 3.3 Verify Upload

1. Check that **"Chunks in Database"** shows a number > 0
2. Try the **Test RAG Search** feature:
   - Enter "Tell me about the Doberman"
   - Click "Test Search"
   - Should return relevant chunks about the loyal cleaner

---

## Step 4: Test the System

### 4.1 Test RAG Search in Admin

1. Go to **Settings → Hive Mistress → RAG**
2. Scroll to **"Test RAG Search"**
3. Try these test queries:

```
"What happens during inspection?"
"Who is the Doberman?"
"What is visitor 6?"
"Tell me about error code 87"
"Explain the three frequencies"
```

Each should return 3-5 relevant lore chunks with similarity scores.

### 4.2 Test Chat Integration

1. Go to the Hive Mistress chat page
2. Send message: "Tell me about the Doberman"
3. AI should respond with Doberman lore (loyal cleaner, service animal, bucket and mop, etc.)
4. Send message: "What is Pink Panthers?"
5. AI should respond with nightclub mythology

**How to Know It's Working:**
- AI responses include specific lore details not in nightly discoveries
- AI references mythology naturally without being prompted
- Responses feel richer and more contextualized

### 4.3 Test Graceful Degradation

1. Temporarily comment out one API key in wp-config.php
2. Try sending a chat message
3. Chat should still work (no RAG, but no errors)
4. Restore the key

---

## Cost Breakdown

### One-Time Upload Cost (OpenAI Embeddings)
- **Model:** text-embedding-3-small
- **Price:** $0.00002 per 1,000 tokens
- **70 files × 10KB average = 700KB text**
- **700KB ÷ 4 chars/token ≈ 175,000 tokens**
- **175,000 × $0.00002 = $3.50 one-time**

### Ongoing Query Cost (OpenAI Embeddings)
- **1 embedding per user message**
- **Average message: 50 tokens**
- **100 messages/day × 50 tokens = 5,000 tokens/day**
- **5,000 × $0.00002 = $0.10/day = $3/month**

### Supabase Costs
- **Free Tier:** 500MB database, 5GB bandwidth/month, unlimited requests
- **Estimated usage:** ~6MB for 1000 chunks with embeddings
- **Well within free tier**

**Total:** ~$3-4/month (mostly OpenAI query embeddings)

---

## Maintenance

### Adding New Lore Files

When you add new worldbuilding documents:

1. Add files to one of the scanned directories (preferably `/Users/ja/g/protocols/`)
2. Go to **Settings → Hive Mistress → RAG**
3. Click **"Upload Lore Files"** again
4. System will process all files (including new ones)
5. Deduplication prevents duplicate chunks

**Note:** Re-uploading is safe - it adds new chunks but doesn't delete old ones unless you click "Clear All Lore" first.

### Updating Existing Files

If you edit an existing lore file:

1. Make your changes
2. Click **"Clear All Lore"** to remove old chunks
3. Click **"Upload Lore Files"** to re-upload everything
4. This ensures the latest content is indexed

### Monitoring

Check **Settings → Hive Mistress → RAG** periodically to:
- Verify chunk count hasn't dropped unexpectedly
- Test search queries to ensure relevance
- Monitor for any configuration errors

### Troubleshooting

**No Results from RAG Search:**
- Check that chunk count > 0
- Verify all three credentials are "Set"
- Try clearing and re-uploading lore
- Check Supabase logs for errors

**Chat Works But No Lore in Responses:**
- RAG might be configured but not finding matches
- Check similarity threshold (0.7 default - try lowering to 0.5)
- Test search in admin to verify results
- Check PHP error logs for RAG errors

**Upload Fails:**
- Check OpenAI API key is valid and has credits
- Check Supabase service_role key permissions
- Check PHP error logs for specific error messages
- Try uploading a single file manually to isolate issue

---

## Technical Details

### Chunk Parameters

**Chunk Size:** 1500 characters
- Balances context preservation with embedding quality
- Most mythology fragments are 500-2000 characters

**Overlap:** 200 characters
- 13% overlap maintains continuity
- Prevents losing context at chunk boundaries

**Match Count:** 3 chunks per query
- Provides diverse context without overwhelming prompt
- 3 chunks ≈ 4500 characters of lore context

**Similarity Threshold:** 0.7 (70% match)
- Balances relevance with recall
- Industry standard for semantic search

### Security

**API Key Storage:**
- wp-config.php constants (recommended)
- Not in database (can't leak via SQL dump)
- Already gitignored

**Supabase RLS:**
- Public reads allowed (needed for RAG)
- Only service_role can write (admin uploads)
- Prevents unauthorized modifications

**WordPress Permissions:**
- Only admins can access RAG admin tab
- Only admins can upload/clear lore
- AJAX requests verify nonces and capabilities

---

## Files Modified

### New Files Created
1. `/inc/hive-mistress-rag.php` - Core RAG functions
2. `/inc/hive-mistress-chunker.php` - Document processing
3. `/docs/rag-setup-guide.md` - This file

### Existing Files Modified
1. `/functions.php` - Added requires for RAG files
2. `/inc/hive-mistress-shortcode.php` - Added RAG tab + AJAX handlers + chat integration
3. `/inc/hive-mistress-prompts.php` - Added RAG context parameter to prompt builder

---

## Support

**Issues:**
- Check WordPress PHP error log: `/wp-content/debug.log` (if WP_DEBUG enabled)
- Check Supabase logs in dashboard
- Check OpenAI usage dashboard for API errors

**Questions:**
- Review plan file: `/Users/ja/.claude/plans/vast-napping-puppy.md`
- Review code comments in RAG files
- Test system step-by-step following this guide

---

## Success Criteria

✅ All three credentials show "Set" in admin
✅ Chunk count shows 900-1200 chunks
✅ Test queries return relevant lore
✅ Chat responses include mythology details
✅ Chat still works if RAG is disabled
✅ No PHP errors in debug log

When all criteria met, RAG system is fully operational!
