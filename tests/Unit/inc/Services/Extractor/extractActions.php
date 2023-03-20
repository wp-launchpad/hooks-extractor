<?php

namespace RocketLauncherHooksExtractor\Tests\Unit\inc\Services\Extractor;

use Mockery;
use RocketLauncherHooksExtractor\Services\Extractor;
use League\Flysystem\Filesystem;


use RocketLauncherHooksExtractor\Tests\Unit\TestCase;

/**
 * @covers \RocketLauncherHooksExtractor\Services\Extractor::extract_actions
 */
class Test_extractActions extends TestCase {

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Extractor
     */
    protected $extractor;

    public function set_up() {
        parent::set_up();
        $this->filesystem = Mockery::mock(Filesystem::class);

        $this->extractor = new Extractor($this->filesystem);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
        $this->assertSame($expected, $this->extractor->extract_actions($config['content']));

    }
}
