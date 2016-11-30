<?php

use Mockery as m;

class ThemeTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    public function tearDown()
    {
        m::close();
    }

    public function setUp()
    {
        $app = app();

        $url = new SleepingOwl\Framework\Routing\UrlGenerator(
            $routes = new Illuminate\Routing\RouteCollection(),
            $router = m::mock(SleepingOwl\Framework\Contracts\Routing\Router::class),
            $request = Illuminate\Http\Request::create('http://www.foo.com/')
        );

        $router->shouldReceive('getUrlPrefix')->andReturn('test_prefix');

        $app['request'] = $request;
        $app->instance(SleepingOwl\Framework\Routing\UrlGenerator::class, $url);

        $this->app = $app;
    }

    /**
     * @param array $config
     *
     * @return \SleepingOwl\Framework\Contracts\Themes\Theme
     */
    public function getThemeObject(array $config = [])
    {
        $meta = m::mock(SleepingOwl\Framework\Contracts\Template\Meta::class);
        $meta->shouldReceive('addJs')->andReturnSelf();
        $meta->shouldReceive('addCss')->andReturnSelf();

        $framework = m::mock(SleepingOwl\Framework\SleepingOwl::class);
        $framework->shouldReceive('name')->andReturn('framework v.0.0.1');

        $theme = new SleepingOwl\Framework\Themes\AdminLteTheme(
            $framework,
            $meta,
            $navigation = m::mock(SleepingOwl\Framework\Contracts\Template\Navigation::class),
            $view = m::mock(Illuminate\Contracts\View\Factory::class),
            $config
        );

        return $theme;
    }

    public function testTitleGenerating()
    {
        $theme = $this->getThemeObject();

        $this->assertEquals('test | framework v.0.0.1', $theme->title('test'));
        $this->assertEquals('framework v.0.0.1', $theme->title());
    }

    public function testAssetPathGenerating()
    {
        $theme = $this->getThemeObject();

        $this->assertEquals($theme->assetDir().'/test', $theme->assetPath('test'));
        $this->assertEquals($theme->assetDir().'/test', $theme->assetPath('/test'));
        $this->assertEquals($theme->assetDir(), $theme->assetPath());
    }

    public function testAssetGenerating()
    {
        $theme = $this->getThemeObject();
        $this->app[\SleepingOwl\Framework\Routing\UrlGenerator::class]->setTheme($theme);

        $this->assertEquals('http://www.foo.com/'.$theme->assetPath('test.js'), $theme->asset('test.js'));
        $this->assertEquals($this->app[\SleepingOwl\Framework\Routing\UrlGenerator::class]->asset('test.js'), $theme->asset('test.js'));
    }

    public function testViewPathGenerating()
    {
        $theme = $this->getThemeObject();

        $this->assertEquals($theme->namespace().'app', $theme->viewPath('app'));
    }

    public function testGettingThemeConfig()
    {
        $theme = $this->getThemeObject();

        $this->assertInstanceOf(\Illuminate\Config\Repository::class, $theme->getConfig());
    }

    public function testGetLogo()
    {
        $theme = $this->getThemeObject();
        $this->assertNotNull($theme->logo());
    }

    public function testGetLogoSmall()
    {
        $theme = $this->getThemeObject();
        $this->assertNotNull($theme->logoSmall());
    }

    public function testOverrideLogo()
    {
        $newLogo = '<img src="new-path-to-logo.jpg" />';
        $theme = $this->getThemeObject([
            'logo' => $newLogo
        ]);

        $this->assertEquals($newLogo, $theme->logo());
        $this->assertNotEquals($newLogo, $theme->logoSmall());
    }

    public function testOverrideLogoSmall()
    {
        $newLogo = '<img src="new-path-to-logo.jpg" />';
        $theme = $this->getThemeObject([
            'logo_small' => $newLogo
        ]);

        $this->assertEquals($newLogo, $theme->logoSmall());
        $this->assertNotEquals($newLogo, $theme->logo());
    }
}