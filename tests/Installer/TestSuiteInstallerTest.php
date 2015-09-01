<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\Installer;

use Moodlerooms\MoodlePluginCI\Bridge\MoodlePlugin;
use Moodlerooms\MoodlePluginCI\Installer\InstallOutput;
use Moodlerooms\MoodlePluginCI\Installer\TestSuiteInstaller;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodle;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Process\DummyExecute;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class TestSuiteInstallerTest extends \PHPUnit_Framework_TestCase
{
    private $tempDir;
    private $pluginDir;

    protected function setUp()
    {
        $this->tempDir   = sys_get_temp_dir().'/moodle-plugin-ci/TestSuiteInstallerTest'.time();
        $this->pluginDir = $this->tempDir.'/plugin';

        $fs = new Filesystem();
        $fs->mkdir($this->tempDir);
        $fs->mirror(__DIR__.'/../Fixture/moodle-local_travis', $this->pluginDir);
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->tempDir);
    }

    public function testInstall()
    {
        $output    = new InstallOutput();
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );
        $installer->setInstallOutput($output);
        $installer->install();

        $this->assertEquals($installer->stepCount(), $output->getStepCount());
    }

    public function testBehatProcesses()
    {
        $output    = new InstallOutput();
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );
        $installer->setInstallOutput($output);

        $this->assertNotEmpty($installer->getBehatInstallProcesses());
        $this->assertNotEmpty($installer->getPostBehatInstallProcesses());

        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests/behat');

        $this->assertEmpty($installer->getBehatInstallProcesses());
        $this->assertEmpty($installer->getPostBehatInstallProcesses());
    }

    public function testPHPUnitProcesses()
    {
        $output    = new InstallOutput();
        $installer = new TestSuiteInstaller(
            new DummyMoodle(''),
            new MoodlePlugin($this->pluginDir),
            new DummyExecute()
        );
        $installer->setInstallOutput($output);

        $this->assertNotEmpty($installer->getPHPUnitInstallProcesses());
        $this->assertNotEmpty($installer->getPostPHPUnitInstallProcesses());

        $fs = new Filesystem();
        $fs->remove($this->pluginDir.'/tests');

        $this->assertEmpty($installer->getPHPUnitInstallProcesses());
        $this->assertEmpty($installer->getPostPHPUnitInstallProcesses());
    }
}