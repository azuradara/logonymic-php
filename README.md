# logonymic

Server-side PHP library for nymic browser fingerprints. Use it to compute a device ID from the data nymic collects in the browser, and to compare two IDs.

## Requirements

- PHP 8.2 or later
- `@azuradara/nymic` in the browser

## Install

```sh
composer require azuradara/logonymic
```

## In the browser

`@azuradara/nymic` is on GitHub Packages. Add the scope to `.npmrc`, with a GitHub token that has `read:packages`:

```ini
@azuradara:registry=https://npm.pkg.github.com
//npm.pkg.github.com/:_authToken=${NODE_AUTH_TOKEN}
```

```sh
pnpm add @azuradara/nymic
```

Collect the fingerprint and send `worker` with the order:

```ts
import { incant } from '@azuradara/nymic'

const { worker } = await incant()

await fetch('/orders', {
  method: 'POST',
  headers: { 'content-type': 'application/json' },
  body: JSON.stringify({ ...order, worker }),
})
```

Without a bundler, load `node_modules/@azuradara/nymic/dist/nymic.js` with a script tag and call `Nymic.incant()`.

## On the server

Compute the nymic from `worker`:

```php
use Logonymic\Nymic;

$nymic = Nymic::of(json_decode($request->getContent())->worker);

$nymic->neonymic;
$nymic->toArray();
```

You can pass `Nymic::of()` a JSON string, a decoded object or an array. In an array, an empty object and an empty list are both stored as `[]`. Pass the raw JSON or an object when you can.

| Field | Value |
| --- | --- |
| `neonymic` | Device and browser ID. Use it as the main ID. |
| `egonymic` | Device ID that stays the same across browser updates |
| `tribunymic` | Hardware model, shared by devices of the same model |
| `protonymic` | OS, browser and device type |
| `paleonymic` | Per-signal fragments, used for `similarity` |

Store `toArray()` with the order.

## Comparing two devices

```php
use Logonymic\Level;
use Logonymic\Nymic;

$result = Nymic::from($order->nymic)->match(Nymic::from($previous->nymic));

$sameDevice = in_array($result->level, [Level::Neonymic, Level::Egonymic], true);
```

You get a `MatchResult` from `match()`, with a `level` and a `similarity` from 0 to 1. Levels, from strongest to weakest.

| Level | Meaning |
| --- | --- |
| `Neonymic` | Same browser on the same device |
| `Egonymic` | Same device, after a browser update |
| `Paleonymic` | `similarity` is at or above the threshold: the same device after a bigger change, or an identical device |
| `Tribunymic` | Same hardware model |
| `None` | No match |

The threshold is `0.8` by default:

```php
$result = $a->match($b, threshold: 0.9);
```

You can get the same ID for two devices of the same model, OS and browser version. Combine the result with order data such as the phone number, address and IP.

## Development

```sh
composer install
composer test
composer lint
```

## License

MIT
