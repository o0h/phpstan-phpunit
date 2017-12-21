<?php declare(strict_types = 1);

namespace PHPStan\Rules\PHPUnit;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;

class AssertRuleHelper
{

	/**
	 * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return bool
	 */
	public static function isMethodOrStaticCallOnTestCase(Node $node, Scope $scope): bool
	{
		$testCaseType = new ObjectType(\PHPUnit\Framework\TestCase::class);
		if ($node instanceof Node\Expr\MethodCall) {
			$calledOnType = $scope->getType($node->var);
		} elseif ($node instanceof Node\Expr\StaticCall) {
			if ($node->class instanceof Node\Name) {
				$class = (string) $node->class;
				if (in_array(
					strtolower($class),
					[
						'self',
						'static',
						'parent',
					],
					true
				)) {
					$calledOnType = new ObjectType($scope->getClassReflection()->getName());
				} else {
					$calledOnType = new ObjectType($class);
				}
			} else {
				$calledOnType = $scope->getType($node->class);
			}
		} else {
			return false;
		}

		if (!$testCaseType->isSuperTypeOf($calledOnType)->yes()) {
			return false;
		}

		return true;
	}

}
