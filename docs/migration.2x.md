---
layout: page
title: Migrating from SocialConnect/Auth 1.x to 2.x
parent: Documentation
nav_order: 3
---

1. PHP 5.x support was dropped, you should upgrade you PHP to `>=7.0`
2. Change parameter `redirectUri` inside configuration to `http://localhost:8000/auth/cb/${provider}/`