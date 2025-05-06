<?php

namespace Tests\Unit\Filament\Widgets;

use App\Enums\ExperienceStatus;
use App\Enums\ListingStatus;
use App\Filament\Widgets\ListingsStatsWidget;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

class ListingsStatsWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function it_displays_correct_pending_items_count(): void
    {
        Listing::factory()->count(3)->create([
            'status' => ListingStatus::PENDING,
            'host_id' => $this->user->id,
        ]);
        Experience::factory()->count(2)->create([
            'status' => ExperienceStatus::PENDING,
            'host_id' => $this->user->id,
        ]);

        Listing::factory()->count(1)->create([
            'status' => ListingStatus::PUBLISHED,
            'host_id' => $this->user->id,
        ]);
        Experience::factory()->count(1)->create([
            'status' => ExperienceStatus::PUBLISHED,
            'host_id' => $this->user->id,
        ]);

        $widget = new ListingsStatsWidget;
        $stats = $this->getProtectedProperty($widget, 'getStats');

        $pendingStat = $this->findStatByTitle($stats, 'Pending Items');
        $this->assertNotNull($pendingStat);
        $this->assertEquals(5, $pendingStat->getValue());
    }

    /**
     * Helper method to get protected property value
     *
     * @throws ReflectionException
     */
    private function getProtectedProperty($object, $method)
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($method);

        return $method->invoke($object);
    }

    /**
     * Helper method to find a stat by its title
     */
    private function findStatByTitle(array $stats, string $title): ?Stat
    {
        $translatedTitle = __('widgets.'.strtolower(str_replace(' ', '_', $title)));

        foreach ($stats as $stat) {
            if ($stat->getLabel() === $translatedTitle) {
                return $stat;
            }
        }

        return null;
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function it_displays_correct_cancelled_items_count(): void
    {
        Listing::factory()->count(2)->create([
            'status' => ListingStatus::SUSPENDED,
            'host_id' => $this->user->id,
        ]);
        Listing::factory()->count(1)->create([
            'status' => ListingStatus::ARCHIVED,
            'host_id' => $this->user->id,
        ]);
        Experience::factory()->count(2)->create([
            'status' => ExperienceStatus::CANCELLED,
            'host_id' => $this->user->id,
        ]);
        Experience::factory()->count(1)->create([
            'status' => ExperienceStatus::SUSPENDED,
            'host_id' => $this->user->id,
        ]);

        Listing::factory()->count(1)->create([
            'status' => ListingStatus::PUBLISHED,
            'host_id' => $this->user->id,
        ]);
        Experience::factory()->count(1)->create([
            'status' => ExperienceStatus::PUBLISHED,
            'host_id' => $this->user->id,
        ]);

        $widget = new ListingsStatsWidget;
        $stats = $this->getProtectedProperty($widget, 'getStats');

        $cancelledStat = $this->findStatByTitle($stats, 'Cancelled Items');
        $this->assertNotNull($cancelledStat);
        $this->assertEquals(6, $cancelledStat->getValue());
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function it_displays_correct_draft_items_count(): void
    {
        Listing::factory()->count(4)->create([
            'status' => ListingStatus::DRAFT,
            'host_id' => $this->user->id,
        ]);
        Experience::factory()->count(3)->create([
            'status' => ExperienceStatus::DRAFT,
            'host_id' => $this->user->id,
        ]);

        Listing::factory()->count(1)->create([
            'status' => ListingStatus::PUBLISHED,
            'host_id' => $this->user->id,
        ]);
        Experience::factory()->count(1)->create([
            'status' => ExperienceStatus::PUBLISHED,
            'host_id' => $this->user->id,
        ]);

        $widget = new ListingsStatsWidget;
        $stats = $this->getProtectedProperty($widget, 'getStats');

        $draftStat = $this->findStatByTitle($stats, 'Draft Items');
        $this->assertNotNull($draftStat);
        $this->assertEquals(7, $draftStat->getValue());
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function it_displays_correct_rejected_items_count(): void
    {
        Listing::factory()->count(2)->create([
            'status' => ListingStatus::REJECTED,
            'host_id' => $this->user->id,
        ]);
        Experience::factory()->count(3)->create([
            'status' => ExperienceStatus::REJECTED,
            'host_id' => $this->user->id,
        ]);

        Listing::factory()->count(1)->create([
            'status' => ListingStatus::PUBLISHED,
            'host_id' => $this->user->id,
        ]);
        Experience::factory()->count(1)->create([
            'status' => ExperienceStatus::PUBLISHED,
            'host_id' => $this->user->id,
        ]);

        $widget = new ListingsStatsWidget;
        $stats = $this->getProtectedProperty($widget, 'getStats');

        $rejectedStat = $this->findStatByTitle($stats, 'Rejected Items');
        $this->assertNotNull($rejectedStat);
        $this->assertEquals(5, $rejectedStat->getValue());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }
}
