<?php declare(strict_types=1);

use Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector;
use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector;
use Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use Rector\CodeQuality\Rector\For_\ForToForeachRector;
use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector;
use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Laravel\Rector\Assign\CallOnAppArrayAccessToStandaloneAssignRector;
use Rector\Laravel\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\UnionTypesRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // get parameters
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_80);

    // skip classes used in PHP DocBlocks, like in /** @var \Some\Class */ [default: true]
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);

    // Run Rector only on changed files
    $parameters->set(Option::ENABLE_CACHE, true);

    $parameters->set(Option::PATHS, [
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    // Define what rule sets will be applied
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::PHP_74);
    $containerConfigurator->import(SetList::PHP_80);
    $containerConfigurator->import(SetList::TYPE_DECLARATION);
//    $containerConfigurator->import(SetList::TYPE_DECLARATION_STRICT);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_CODE_QUALITY);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_EXCEPTION);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_MOCK);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_YIELD_DATA_PROVIDER);

    $parameters->set(Option::SKIP, [
        // From PHP 8.0 SetList, we are still not sure about these features
        ClassPropertyAssignToConstructorPromotionRector::class,
        UnionTypesRector::class, // temp, need to make some work and test changes, especially with middleware: some PHPDoc info can be not reliable

        AddLiteralSeparatorToNumberRector::class, // we format numbers by our rules (e.g. currencies)
        TypedPropertyRector::class, // we have problems with Serializer from Notification System v2
        AddClosureReturnTypeRector::class,
        ClosureToArrowFunctionRector::class, // it may make code less readable in some cases
        ArrayThisCallToThisMethodCallRector::class, // temp, @see https://github.com/rectorphp/rector/issues/4516
        CallableThisArrayToAnonymousFunctionRector::class, // temp, @see https://github.com/rectorphp/rector/issues/4516
        DateTimeToDateTimeInterfaceRector::class, // converts Carbon to \DateTimeInterface

        ReturnTypeDeclarationRector::class, // changes some return types from interfaces to more concrete implementations, eloquent builder to Database\Query\Builder, HasOne to Builder, etc.
        AddArrayParamDocTypeRector::class, // adds return type from PHPDoc to native PHP and thus breaks Laravel Feature tests where \Illuminate\Testing\TestResponse returned
        ParamTypeDeclarationRector::class, // Adds to concrete types that we don’t need in some cases e.g. abstract public function sendNow(\App\Models\Notification\MessageQueueEmail|\App\Models\Notification\MessageQueuePostcard|\App\Models\Notification\MessageQueueSms $message)
        AddArrayReturnDocTypeRector::class, // uses too concrete return types

        // Laravel
        CallOnAppArrayAccessToStandaloneAssignRector::class,
        ChangeQueryWhereDateValueWithCarbonRector::class,
        // \Rector\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector::class,

        // temp
        \Rector\Php80\Rector\ClassMethod\OptionalParametersAfterRequiredRector::class, // @see https://github.com/rectorphp/rector/issues/6506
    ]);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // register a single rule
    $services->set(ArrayKeyExistsTernaryThenValueToCoalescingRector::class);
    $services->set(CombineIfRector::class);
    $services->set(CombinedAssignRector::class);
    $services->set(CompactToVariablesRector::class);
    $services->set(CompleteDynamicPropertiesRector::class);
    $services->set(ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class);
    $services->set(ExplicitBoolCompareRector::class);
    $services->set(ForRepeatedCountToOwnVariableRector::class);
    $services->set(ForToForeachRector::class);
    $services->set(BinarySwitchToIfElseRector::class);
    $services->set(CallUserFuncArrayToVariadicRector::class);
    $services->set(ConsistentImplodeRector::class);
    $services->set(MakeInheritedMethodVisibilitySameAsParentRector::class);
    $services->set(NullableCompareToNullRector::class);
    $services->set(SplitDoubleAssignRector::class);
    $services->set(SplitStringClassConstantToClassConstFetchRector::class);
    $services->set(StrictArraySearchRector::class);
    $services->set(FinalizeClassesWithoutChildrenRector::class);
    // Laravel specific
    // $services->set(\Rector\Laravel\Rector\StaticCall\RequestStaticValidateToInjectRector::class);

    # For more rectors {@see https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md}
};
