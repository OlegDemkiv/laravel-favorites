<?php

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Artisan;
use Sugar\Favorites\Likeable;

class CommonUseTest extends TestCase
{
	public function setUp()
	{
		parent::setUp();
		
		Eloquent::unguard();

		$this->artisan('migrate', [
		    '--database' => 'testbench',
		    '--realpath' => realpath(__DIR__.'/../migrations'),
		]);
	}
	
	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('favorites.lifetime', 0);
		$app['config']->set('favorites.sessions', true);
		$app['config']->set('favorites.clean_only_session_likes', false);
		$app['config']->set('database.default', 'testbench');
	    $app['config']->set('database.connections.testbench', [
	        'driver'   => 'sqlite',
	        'database' => ':memory:',
	        'prefix'   => '',
	    ]);
	    
		\Schema::create('books', function ($table) {
			$table->increments('id');
			$table->string('name');
			$table->timestamps();
		});
	}
	
	public function tearDown()
	{
		\Schema::drop('books');
	}

	public function test_basic_like()
	{
		$stub = Stub::create(['name'=>123]);
		
		$stub->like(1);
		
		$this->assertEquals(1, $stub->likeCount);
	}
	
	public function test_multiple_likes()
	{
		$stub = Stub::create(['name'=>123]);
		
		$stub->like(1);
		$stub->like(2);
		$stub->like(3);
		$stub->like(4);
		
		$this->assertEquals(4, $stub->likeCount);
	}
	
	public function test_unlike()
	{
		$stub = Stub::create(['name'=>123]);
		
		$stub->unlike(1);
		
		$this->assertEquals(0, $stub->likeCount);
	}
	
	public function test_where_liked_by()
	{
		Stub::create(['name'=>'A'])->like(1);
		Stub::create(['name'=>'B'])->like(1);
		Stub::create(['name'=>'C'])->like(1);
		
		$stubs = Stub::whereLikedBy(1)->get();
		$shouldBeEmpty = Stub::whereLikedBy(2)->get();
		
		$this->assertEquals(3, $stubs->count());
		$this->assertEmpty($shouldBeEmpty);
	}

	public function test_clean_command()
	{
		$stub = Stub::create(['name'=>123]);

		$stub->like(1);
		$stub->like(2);
		$stub->like(3);
		$stub->like(4);

		Artisan::call('favorites:clean');

		$this->assertEquals(0, $stub->likeCount);
	}

	public function test_session_to_user_likes_convert()
	{
		$helper = new \Sugar\Favorites\Helper();
		$stub = Stub::create(['name'=>123]);

		$stub->like('user_1', false);
		$stub->like('session_1', true);

		$helper->convertSessionToUserLikes('session_1', 'user_1');

		$this->assertEquals(1, $stub->likeCount);
	}
}

class Stub extends Eloquent
{
	use Likeable;
	
	protected $morphClass = 'Stub';
	
	protected $connection = 'testbench';
	
	public $table = 'books';
}
