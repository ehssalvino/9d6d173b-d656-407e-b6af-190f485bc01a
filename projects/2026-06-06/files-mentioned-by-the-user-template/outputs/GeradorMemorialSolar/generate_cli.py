from __future__ import annotations

import argparse

from memorial import GenerationError, generate_memorial


def main() -> int:
    parser = argparse.ArgumentParser(description="Gera memorial solar a partir do Excel e do template.")
    parser.add_argument("--excel", required=True)
    parser.add_argument("--template", required=True)
    parser.add_argument("--out", required=True)
    args = parser.parse_args()
    try:
        result = generate_memorial(args.excel, args.template, args.out)
    except GenerationError as exc:
        parser.error(str(exc))
    print(f"Memorial gerado: {result.output}")
    print(f"Substituições: {result.replaced_tags}")
    if result.missing_values:
        print("Campos vazios:", ", ".join(result.missing_values))
    if result.unresolved_tags:
        print("Tags não resolvidas:", ", ".join(result.unresolved_tags))
        return 2
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
