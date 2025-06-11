# Progressive Test Coverage Improvement Prompt for Augment Code

## CRITICAL RULES - DO NOT VIOLATE:

- **NEVER delete or modify existing test files**
- **NEVER remove existing test methods**
- **ONLY add new tests or enhance existing ones by adding new test methods**
- **Always preserve all existing test code**

## TEST QUALITY REQUIREMENTS - MANDATORY:

- **ALL tests MUST PASS** - No failing, warning, risky, or skipped tests allowed
- **Tests must be meaningful** - Actually test the behavior, not just execute code
- **Use proper assertions** - verify expected outcomes, not just that code runs
- **Test edge cases and error conditions** - don't just test happy paths
- **Each test method should test ONE specific behavior**
- **Use descriptive test names** that explain what is being tested

## VERIFICATION BEFORE MILESTONE COMMIT:

**BEFORE committing, you MUST verify:**

1. `composer test` - ALL tests pass (green)
2. `composer test:coverage` - Coverage target reached AND all tests pass
3. **Zero failures, warnings, risky, or skipped tests**
4. **If ANY test fails, fix it before proceeding**

## PROGRESSIVE MILESTONE SYSTEM:

**Current minimum threshold: 53%**
**Next milestone target: 65%**
**Final goal: 95%**

## WORKFLOW:

1. **PRIORITY: Focus on 0% coverage files first** - Always target completely untested files/classes before improving existing test coverage
2. **Add tests incrementally** until coverage crosses the current milestone target
3. **Verify all tests pass** with `composer test`
4. **When milestone is reached AND all tests pass:**
   - Run `composer test:coverage` to verify coverage AND test success
   - Report: "Milestone reached: X.X% coverage with all tests passing."
   - **Ask: "Should I commit these changes? (Please verify with your own test run first)"**
   - **WAIT for human approval ("yes" or "no")**
5. **If approved:** Commit with `git add . && git commit -m "chore: upgrade test coverage to X.X%"`
6. **Human will update thresholds, then say "continue"**
7. **Resume testing until next milestone**

## MILESTONES PROGRESSION (update as you go):

- [ ] 53% → 65%
- [ ] 65% → 75%
- [ ] 75% → 85%
- [ ] 85% → 95%

## INSTRUCTIONS:

1. **PRIORITIZE 0% coverage files** - Always target completely untested classes/files first for maximum coverage impact
2. **Analyze uncovered code** - identify untested methods/classes/branches, starting with 0% files
3. **Write QUALITY tests** - meaningful assertions that verify correct behavior
4. **One file at a time** - work on single classes systematically
5. **Ensure tests pass** with `composer test` after each addition
6. **Check coverage** with `composer test:coverage`
7. **Only ask for commit approval when ALL tests pass AND milestone reached**

## GOOD TEST EXAMPLES:

```php
// ✅ GOOD - Tests specific behavior with meaningful assertions
test('user can be created with valid data', function () {
    $userData = ['name' => 'John', 'email' => 'john@example.com'];
    $user = User::create($userData);

    expect($user->name)->toBe('John');
    expect($user->email)->toBe('john@example.com');
    expect($user->exists)->toBeTrue();
});

// ✅ GOOD - Tests error conditions
test('user creation fails with invalid email', function () {
    expect(fn() => User::create(['email' => 'invalid']))
        ->toThrow(ValidationException::class);
});
```

## BAD TEST EXAMPLES:

```php
// ❌ BAD - Just executes code without meaningful assertions
test('some test', function () {
    $user = new User();
    $user->getName(); // No assertions!
});

// ❌ BAD - Skipped or incomplete tests
test('todo: write this test', function () {
    $this->markTestSkipped('Will write later');
});
```

## RESPONSE FORMAT:

- **Show the new test code being added**
- **Use format: "Adding tests for [ClassName]"**
- **Include only the new test methods, not entire files**
- **When milestone reached: "Milestone reached: X.X% coverage with all tests passing. Should I commit these changes? (Please verify with your own test run first)"**

## CURRENT STATUS:

- Current coverage: 53.5%
- Target milestone: 65%
- Framework: Kasi (Laravel Lumen fork)
- Test runner: Pest PHP
- Coverage command: `composer test:coverage`

## HUMAN APPROVAL & VERIFICATION PROCESS:

**When Augment asks for commit approval:**

1. Run your own verification:
   ```bash
   composer test          # Verify all tests pass
   composer test:coverage  # Verify coverage reached
   ```
2. Review the test code quality
3. Respond to Augment:
   - **"yes"** - Augment commits automatically
   - **"no"** - Augment should fix issues before asking again
4. After successful commit, update thresholds in coverage.md
5. Tell Augment: "continue"

---

**Remember: QUALITY OVER QUANTITY. ALL TESTS MUST PASS. ASK FOR APPROVAL BEFORE COMMITTING.**
