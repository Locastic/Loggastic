# Contributing to Loggastic

Thanks for considering a contribution! Bug reports, fixes, docs improvements,
and features are all welcome.

## Setup

```bash
git clone git@github.com:<your-username>/Loggastic.git
cd Loggastic
composer install
```

The functional tests need Elasticsearch 7.x on `localhost:9200`:

```bash
docker run -d --name loggastic-es7 -p 9200:9200 -e discovery.type=single-node \
  docker.elastic.co/elasticsearch/elasticsearch:7.17.28
```

## Running the checks

All of these must pass before a PR can be merged (CI runs them too):

```bash
vendor/bin/simple-phpunit                                   # tests (unit only: tests/UnitTests)
vendor/bin/phpstan analyse --memory-limit=1G                # static analysis
vendor/bin/php-cs-fixer fix                                 # coding standards (auto-fixes)
composer validate --strict
```

New code should follow the existing conventions: `final` classes by default,
an `Interface`-suffixed interface per service colocated in the same directory,
constructor property promotion with `readonly` collaborators, and
Elasticsearch-specific code only under `Bridge/Elasticsearch/`.

## Submitting a pull request

- Base your branch on `master`.
- Fill in the PR template (the Q/A table helps us triage quickly).
- Add a CHANGELOG entry under "Unreleased" for any user-visible change.
- Public class/method signatures, service IDs, container parameters, and
  config keys are all covered by our backward-compatibility policy — breaking
  them requires a deprecation first and is only released in a major version.
- New tests are expected for new behavior; bug fixes ideally come with a test
  that fails without the fix.

## Reporting bugs

Use the issue template. The most useful reports include: Loggastic version,
Symfony version, PHP version, Elasticsearch version, and a minimal
reproduction (config + entity + the action that misbehaves).

## Security issues

Never open a public issue for a vulnerability — see [SECURITY.md](SECURITY.md).
