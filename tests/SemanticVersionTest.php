<?php
/**
 * Copyright Andreas Heigl <andreas@heigl.org>
 *
 * Licenses under the MIT-license. For details see the included file LICENSE.md
 */

namespace PharIo\SinglePharPluginBaseTest;

use Generator;
use PharIo\SinglePharPluginBase\Exception\NoSemanticVersioning;
use PharIo\SinglePharPluginBase\SemanticVersion;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class SemanticVersionTest extends TestCase
{
	/**
	 * @param string $version
	 * @param int $major
	 * @param int $minor
	 * @param int $patch
	 * @param string $preRelease
	 * @param string $build
	 *
	 * @dataProvider semVerIsCorrectlySplitUpProvider
	 * @covers       \PharIo\SinglePharPluginBase\SemanticVersion::fromVersionString
	 */
	public function testThatSemVerIsCorrectlySplitUp(
		string $version,
		int $major,
		int $minor,
		int $patch,
		string $preRelease,
		string $build
	)
	{
		$semver = SemanticVersion::fromVersionString($version);

		Assert::assertSame($major, $semver->major());
		Assert::assertSame($minor, $semver->minor());
		Assert::assertSame($patch, $semver->patch());
		Assert::assertSame($preRelease, $semver->preRelease());
		Assert::assertSame($build, $semver->build());
	}

	public function semVerIsCorrectlySplitUpProvider() : Generator
	{
		yield ['1.2.3-RC01+123', 1, 2, 3, 'RC01', '123'];
		yield ['1.2.3-RC01', 1, 2, 3, 'RC01', ''];
		yield ['1.2.3+123', 1, 2, 3, '', '123'];
		yield ['1.2.3', 1, 2, 3, '', ''];
	}

	/**
	 * @param string $version
	 * @param int $major
	 * @param int $minor
	 * @param int $patch
	 * @param string $preRelease
	 * @param string $build
	 *
	 * @dataProvider exceptionIsThrownOnIncorrectSemVerProvider
	 * @covers       \Phive\ComposerPharMetaPlugin\SemanticVersion::fromVersionString
	 */
	public function testExceptionIsThrownOnIncorrectSemVer(string $version)
	{
		self::expectException(NoSemanticVersioning::class);
		$semver = SemanticVersion::fromVersionString($version);
	}

	public function exceptionIsThrownOnIncorrectSemVerProvider() : Generator
	{
		yield ['1.2.3RC01'];
		yield ['1.2'];
		yield ['test'];
		yield ['1test'];
	}

}
