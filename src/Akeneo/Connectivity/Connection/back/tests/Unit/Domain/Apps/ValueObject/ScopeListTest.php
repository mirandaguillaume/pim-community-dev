<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Unit\Domain\Apps\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PHPUnit\Framework\TestCase;

class ScopeListTest extends TestCase
{
    private ScopeList $sut;

    protected function setUp(): void
    {
    }

    public function test_it_is_a_scope_list(): void
    {
        $this->assertTrue(\is_a(ScopeList::class, ScopeList::class, true));
    }

    public function test_it_is_instantiable_from_a_string_of_scopes(): void
    {
        $this->sut = ScopeList::fromScopeString(\sprintf(
            "%s %s %s",
            AuthenticationScope::SCOPE_EMAIL,
            AuthenticationScope::SCOPE_PROFILE,
            AuthenticationScope::SCOPE_OPENID
        ), );
        $this->assertInstanceOf(ScopeList::class, $this->sut);
    }

    public function test_it_is_instantiable_from_an_array_of_scopes(): void
    {
        $this->sut = ScopeList::fromScopes([
                            AuthenticationScope::SCOPE_EMAIL,
                            AuthenticationScope::SCOPE_PROFILE,
                            AuthenticationScope::SCOPE_OPENID,
                        ], );
        $this->assertInstanceOf(ScopeList::class, $this->sut);
    }

    public function test_it_gets_scopes(): void
    {
        $this->sut = ScopeList::fromScopeString(\sprintf(
            "%s %s %s",
            AuthenticationScope::SCOPE_EMAIL,
            AuthenticationScope::SCOPE_PROFILE,
            AuthenticationScope::SCOPE_OPENID
        ), );
        $this->assertSame([
                    AuthenticationScope::SCOPE_EMAIL,
                    AuthenticationScope::SCOPE_OPENID,
                    AuthenticationScope::SCOPE_PROFILE,
                ], $this->sut->getScopes());
    }

    public function test_it_adds_scopes(): void
    {
        $this->sut = ScopeList::fromScopes([
                            AuthenticationScope::SCOPE_EMAIL,
                            AuthenticationScope::SCOPE_PROFILE,
                            AuthenticationScope::SCOPE_OPENID,
                        ], );
        $newScopesList = $this->sut->addScopes(ScopeList::fromScopes(['new_scope', 'another_new_scope']));
        $this->assertSame([
                    'another_new_scope',
                    'email',
                    'new_scope',
                    'openid',
                    'profile',
                ], $newScopesList->getScopes());
    }

    public function test_it_tests_if_a_scope_belongs_to_scope_list(): void
    {
        $this->sut = ScopeList::fromScopes(['scope']);
        $this->assertSame(true, $this->sut->hasScope('scope'));
        $this->assertSame(false, $this->sut->hasScope('not_found_scope'));
    }

    public function test_it_gets_scopes_has_a_string(): void
    {
        $this->sut = ScopeList::fromScopes(['a_scope', 'another_scope']);
        $this->assertSame("a_scope another_scope", $this->sut->toScopeString());
    }

    public function test_it_compares_two_different_scope_lists(): void
    {
        $differentList = ScopeList::fromScopeString('another_scope other_scope');
        $biggerList = ScopeList::fromScopeString('a_scope another_scope other_scope');
        $smallerList = ScopeList::fromScopeString('a_scope');
        $emptyList = ScopeList::fromScopeString('');
        $this->sut = ScopeList::fromScopeString('');
        $this->assertSame(false, $this->sut->equals($differentList));
        $this->assertSame(false, $this->sut->equals($biggerList));
        $this->assertSame(false, $this->sut->equals($smallerList));
        $scopeList = $this->sut->addScopes(ScopeList::fromScopeString('a_scope another_scope'));
        $this->assertFalse($scopeList->equals($differentList));
        $this->assertFalse($scopeList->equals($biggerList));
        $this->assertFalse($scopeList->equals($smallerList));
        $this->assertFalse($scopeList->equals($emptyList));
    }

    public function test_it_compares_the_same_two_scope_lists(): void
    {
        $emptyList = ScopeList::fromScopeString('');
        $this->sut = ScopeList::fromScopeString('');
        $this->assertSame(true, $this->sut->equals($emptyList));
        $oneItemList = ScopeList::fromScopeString('a_scope');
        $scopeList = $this->sut->addScopes(ScopeList::fromScopeString('a_scope'));
        $this->assertTrue($scopeList->equals($oneItemList));
        $biggerList = ScopeList::fromScopeString('a_scope another_scope other_scope');
        $scopeList = $this->sut->addScopes(ScopeList::fromScopeString('a_scope another_scope other_scope'));
        $this->assertTrue($scopeList->equals($biggerList));
        $rearrangedList = ScopeList::fromScopeString('another_scope other_scope a_scope');
        $this->assertTrue($scopeList->equals($rearrangedList));
    }
}
