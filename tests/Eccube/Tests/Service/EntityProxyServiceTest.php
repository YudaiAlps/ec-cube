<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2017 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Eccube\Tests\Service;

use Eccube\Annotation\EntityExtension;
use Eccube\Service\EntityProxyService;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

class EntityProxyServiceTest extends \PHPUnit_Framework_TestCase
{
    private $tempOutputDir;

    protected function setUp()
    {
        $this->tempOutputDir = tempnam(sys_get_temp_dir(), 'ProxyGeneratorTest');
        unlink($this->tempOutputDir);
        mkdir($this->tempOutputDir);
    }

    protected function tearDown()
    {
        foreach (glob($this->tempOutputDir.'/*') as $file) {
            unlink($file);
        }
        rmdir($this->tempOutputDir);
    }

    public function testGenerate()
    {
        $generator = new EntityProxyService();
        $generator->generate([__DIR__], [], $this->tempOutputDir);

        $generatedFile = $this->tempOutputDir.'/Product.php';
        self::assertTrue(file_exists($generatedFile));

        // Traitのuse句があるかどうか
        $tokens = Tokens::fromCode(file_get_contents($generatedFile));
        $sequence = $tokens->findSequence([
            [CT::T_USE_TRAIT],
            [T_NS_SEPARATOR],
            [T_STRING, 'Eccube'],
            [T_NS_SEPARATOR],
            [T_STRING, 'Tests'],
            [T_NS_SEPARATOR],
            [T_STRING, 'Service'],
            [T_NS_SEPARATOR],
            [T_STRING, 'EntityProxyServiceTest_ProductTrait'],
        ]);
        self::assertNotNull($sequence);
    }

    public function testGenerateExcluded()
    {
        $generator = new EntityProxyService();
        $generator->generate([__DIR__], [], $this->tempOutputDir);

        $generatedFile = $this->tempOutputDir.'/Product.php';
        self::assertTrue(file_exists($generatedFile));

        $tokens = Tokens::fromCode(file_get_contents($generatedFile));
        $traitTokens = [
            [CT::T_USE_TRAIT],
            [T_NS_SEPARATOR],
            [T_STRING, 'Eccube'],
            [T_NS_SEPARATOR],
            [T_STRING, 'Tests'],
            [T_NS_SEPARATOR],
            [T_STRING, 'Service'],
            [T_NS_SEPARATOR],
            [T_STRING, 'EntityProxyServiceTest_ProductTrait'],
        ];

        self::assertNotNull($tokens->findSequence($traitTokens), 'Traitはあるはず');

        // 除外して生成
        $generator->generate([], [__DIR__], $this->tempOutputDir);
        $tokens = Tokens::fromCode(file_get_contents($generatedFile));
        self::assertNull($tokens->findSequence($traitTokens), 'Traitが外されているはず');
    }
}

/**
 * @EntityExtension("Eccube\Entity\Product")
 */
trait EntityProxyServiceTest_ProductTrait
{
    public $testProperty;
}