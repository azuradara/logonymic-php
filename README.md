# logonymic

Recompute and match nymic browser fingerprints in PHP.

In Elder Scrolls lore, the Bastion Nymic of Hermaeus Mora is defended by the Logonymic Wards. With this library, you read on the server the names that nymic collects in the browser.

## Install

```sh
composer require azuradara/logonymic
```

PHP 8.1 or later.

## Usage

Send the `worker` object from `incant()` to your server, then recompute its nymic there. The hashes the browser sends are not used.

```php
use Logonymic\Nymic;

$nymic = Nymic::of($request->getContent());

$nymic->neonymic;
$nymic->toArray();
```

Pass the `worker` to `Nymic::of()` as a JSON string, a decoded object or an array. Prefer the JSON string or an object: in an array, an empty object and an empty list are both decoded as `[]`.

You get five parts.

| Part | What it names |
| --- | --- |
| `protonymic` | The type: OS, browser and device type |
| `tribunymic` | The hardware family, shared by phones of one model |
| `neonymic` | This browser on this device. Use it as the ID. |
| `egonymic` | This device across browser updates |
| `paleonymic` | Fragments for similarity, 2 hex characters per stable signal |

## Matching

```php
use Logonymic\Level;
use Logonymic\Nymic;

$result = Nymic::from($order->nymic)->match(Nymic::from($previous->nymic));

$result->level === Level::Egonymic;
$result->similarity;
```

You can pass `match()` a threshold (default `0.8`). You get the first part that matched, as a `Level`.

- `Neonymic`: same browser on the same device.
- `Egonymic`: same device, most likely after a browser update.
- `Paleonymic`: `similarity` at or above the threshold. Same device after a bigger change, or another device of the same model and setup.
- `Tribunymic`: same hardware family.
- `None`: anything else.

`similarity` is the share of `paleonymic` fragments with the same value. Match on these together with order data (phone, address, IP).

## Compatibility

We follow the nymic porting spec and run its test vectors (`tests/vectors.json`) in every test run. When nymic changes its vectors, we release a new version here.

## Development

```sh
composer install
composer test
composer lint
```

## License

MIT
