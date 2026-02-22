<?php

namespace Alexisgt01\CmsCore\Console\Commands;

use Alexisgt01\CmsCore\Models\BlogSetting;
use Illuminate\Console\Command;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\Crawler;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'cms:sitemap
        {--url= : Base URL to crawl (overrides settings)}
        {--output=public/sitemap.xml : Output file path}
        {--max-urls= : Maximum URLs to crawl (overrides settings)}
        {--concurrency= : Concurrent requests (overrides settings)}
        {--depth= : Maximum crawl depth (overrides settings)}';

    protected $description = 'Generate a sitemap by HTTP crawling the site';

    protected int $crawledCount = 0;

    protected int $skippedCount = 0;

    public function handle(): int
    {
        $settings = BlogSetting::instance();

        if (! $settings->sitemap_enabled) {
            $this->warn('Sitemap generation is disabled in settings.');

            return self::SUCCESS;
        }

        $baseUrl = $this->option('url') ?? $settings->sitemap_base_url ?? config('app.url');

        if (! $baseUrl) {
            $this->error('No base URL configured. Set sitemap_base_url in settings or use --url option.');

            return self::FAILURE;
        }

        $maxUrls = (int) ($this->option('max-urls') ?? $settings->sitemap_max_urls ?? 5000);
        $concurrency = (int) ($this->option('concurrency') ?? $settings->sitemap_concurrency ?? 10);
        $depth = (int) ($this->option('depth') ?? $settings->sitemap_crawl_depth ?? 10);
        $outputPath = $this->option('output');
        $changeFreq = $settings->sitemap_default_change_freq ?? 'weekly';
        $priority = (float) ($settings->sitemap_default_priority ?? 0.5);
        $excludePatterns = $settings->sitemap_exclude_patterns ?? [];

        $this->info("Crawling {$baseUrl}...");
        $this->info("Config: max={$maxUrls}, concurrency={$concurrency}, depth={$depth}");

        $startTime = microtime(true);

        $generator = SitemapGenerator::create($baseUrl)
            ->setMaximumCrawlCount($maxUrls)
            ->shouldCrawl(function (UriInterface $url) use ($baseUrl, $excludePatterns): bool {
                $baseHost = parse_url($baseUrl, PHP_URL_HOST);

                if ($url->getHost() !== $baseHost) {
                    return false;
                }

                $path = $url->getPath();

                foreach ($excludePatterns as $pattern) {
                    if (fnmatch($pattern, $path)) {
                        $this->skippedCount++;

                        return false;
                    }
                }

                return true;
            })
            ->hasCrawled(function (Url $url, ?ResponseInterface $response) use ($changeFreq, $priority): ?Url {
                if ($response && $response->getStatusCode() !== 200) {
                    $this->skippedCount++;

                    return null;
                }

                $this->crawledCount++;

                if ($this->crawledCount % 50 === 0) {
                    $this->output->write("\r  Crawled: {$this->crawledCount} URLs...");
                }

                return $url
                    ->setChangeFrequency($changeFreq)
                    ->setPriority($priority);
            })
            ->configureCrawler(function (Crawler $crawler) use ($concurrency, $depth): void {
                $crawler
                    ->setConcurrency($concurrency)
                    ->setMaximumDepth($depth)
                    ->ignoreRobots()
                    ->setDelayBetweenRequests(50)
                    ->setParseableMimeTypes(['text/html'])
                    ->setMaximumResponseSize(1024 * 1024 * 2)
                    ->doNotExecuteJavaScript();
            });

        $generator->writeToFile($outputPath);

        $elapsed = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info("Sitemap generated: {$outputPath}");
        $this->info("URLs indexed: {$this->crawledCount} | Skipped: {$this->skippedCount} | Time: {$elapsed}s");

        return self::SUCCESS;
    }
}
