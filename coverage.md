# Progressive Test Coverage Improvement Prompt for Augment Code

## CRITICAL RULES - DO NOT VIOLATE:

- **NEVER delete or modify existing test files**
- **NEVER remove existing test methods**
- **NEVER allow warnings or risky changes**
- **ONLY add new tests or enhance existing ones by adding new test methods**
- **Always preserve all existing test code**

## PROGRESSIVE MILESTONE SYSTEM:

**Current minimum threshold: 53%**
**Next milestone target: 65%**
**Final goal: 95%**

## WORKFLOW:

1. **Add tests incrementally** until coverage crosses the current milestone target
2. **When milestone is reached:**
   - Run `composer test:coverage` to verify
   - Run `git add .`
   - Run `git commit -m "chore: upgrade test coverage to X.X%"` (use actual percentage)
   - **STOP and wait for human to update minimum threshold**
3. **Human will update the minimum and next milestone, then say "continue"**
4. **Resume testing until next milestone**

## MILESTONES PROGRESSION (update as you go):

- [ ] 53% → 65%
- [ ] 65% → 75%
- [ ] 75% → 85%
- [ ] 85% → 95%

## INSTRUCTIONS:

1. **Analyze uncovered code** - identify untested methods/classes/branches
2. **Add focused tests** - create new test methods for uncovered areas only
3. **One file at a time** - work on single classes systematically
4. **Check coverage frequently** with `composer test:coverage`
5. **Commit and stop when milestone reached**

## RESPONSE FORMAT:

- **No explanations or commentary**
- **Only show the new test code being added**
- **Use format: "Adding tests for [ClassName]"**
- **Include only the new test methods, not entire files**
- **When milestone reached: "Milestone reached: X.X% coverage. Committing changes."**

## CURRENT STATUS:

- Current coverage: 53.5%
- Target milestone: 65%
- Framework: Kasi (Laravel Lumen fork)
- Test runner: Pest PHP
- Coverage command: `composer test:coverage`

## COMMIT WHEN MILESTONE REACHED:

```bash
composer test:coverage
git add .
git commit -m "chore: upgrade test coverage to X.X%"
```

---

**Remember: ADD ONLY. NEVER DELETE. COMMIT AT MILESTONES. WAIT FOR HUMAN TO UPDATE THRESHOLDS.**
