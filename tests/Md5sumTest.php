<?php
namespace Ottosmops\Md5sum\Test;

use Ottosmops\Md5sum\Md5sum;

use Ottosmops\Md5sum\Exceptions\FileNotFound;
use Ottosmops\Md5sum\Exceptions\SeparatorNotFound;

use PHPUnit\Framework\TestCase;

class Md5sumTest extends TestCase
{
    protected $valide_dir = __DIR__.'/testfiles/valide/';

    protected $non_valide_dir = __DIR__.'/testfiles/non-valide/';

    protected $valide_dir_binary = __DIR__.'/testfiles/valide-binary/';

    protected $file_not_in_md5sums = __DIR__.'/testfiles/file-not-in-md5sums/';

    protected $file_not_in_dir = __DIR__.'/testfiles/file-not-in-dir/';

    protected $temp = __DIR__.'/testfiles/temp/';

    /** @test */
    public function it_can_valdiate_a_valide_md5sums()
    {
        $md5sums = $this->valide_dir.'md5sums';
        $md5 = New Md5sum();
        $this->assertTrue($md5->verifyMd5sums($md5sums));
    }

     /** @test */
    public function it_can_valdiate_a_valide_md5sums_binary()
    {
        $md5sums = $this->valide_dir_binary.'md5sums';
        $md5 = New Md5sum();
        $this->assertTrue($md5->verifyMd5sums($md5sums));
    }

    /** @test */
    public function it_can_recognize_file_not_in_dir()
    {
        $md5sums = $this->file_not_in_dir.'md5sums';
        $md5 = New Md5sum();
        $this->assertFalse($md5->verifyMd5sums($md5sums));
        $this->assertSame($md5->messages[1], 'error line 1: could not find test');
    }

    /** @test */
    public function it_will_throw_an_exception_when_file_is_not_found()
    {
        $this->expectException(FileNotFound::class);
        $text = (new Md5sum())->verifyMd5sums('/no/md5sums/here/md5sums');
    }

    /** @test */
    public function it_can_create_a_deep_md5sums()
    {
        $md5 = New Md5sum();
        $md5->createMd5sums($this->temp);
        $this->assertTrue($md5->verifyMd5sums($this->temp .'md5sums'));
        unlink ($this->temp .'md5sums');
    }

    /** @test */
    public function it_can_create_md5sums_with_name()
    {
        $md5 = New Md5sum();
        $md5->createMd5sums($this->temp, 'name');
        $this->assertTrue($md5->verifyMd5sums($this->temp .'name'));
        unlink ($this->temp .'name');
    }

    /** @test */
    public function it_can_create_md5sums_flat()
    {
        $md5 = New Md5sum();
        $this->assertSame($md5->createMd5sums($this->temp, 'md5sums', false), 2);
        $this->assertTrue($md5->verifyMd5sums($this->temp .'md5sums'));
        unlink ($this->temp .'md5sums');
    }

    /** @test */
    public function it_recoginzes_corrupted_files()
    {
        $md5sums = $this->non_valide_dir.'md5sums';
        $md5 = New Md5sum();
        $this->assertFalse($md5->verifyMd5sums($md5sums));
        $this->assertSame($md5->messages[1], 'error line 1: could not verify line d41d8cd98f00b204e9800998ecf8427e  test');
    }
}
