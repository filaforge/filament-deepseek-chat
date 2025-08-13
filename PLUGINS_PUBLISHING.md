# Filaforge Plugins Publishing Guide

This repository contains multiple packages in `plugins/*`. To publish to Packagist, mirror or split each plugin into its own repository.

## Options

1) Manual split (simple)
- Create a new GitHub repo for each package.
- Copy the plugin folder contents into each repo root.
- Push and tag v0.1.0.
- Submit the repo to Packagist and enable GitHub webhook.

2) Automated split (monorepo)
- Use splitsh-lite or a GitHub Action to mirror `plugins/<name>` into separate repos.
- Tag in the monorepo; the workflow pushes tags to the split repositories.

## Tagging

- Use semantic versions. First release: v0.1.0
- After pushing, create a GitHub release per repo (optional) and Packagist will pick it up.

## Notes

- composer.json in each plugin is prepared with metadata (license, keywords, homepage, authors, support).
- CHANGELOG.md, README.md, and LICENSE are present.
- CI workflow is in the monorepo; replicate per package if you want package-level CI.
