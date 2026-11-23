#!/usr/bin/env python3
"""Jejak wordmark tikumpost.id menjadi path SVG untuk partial Blade.

Turunkan resolusi setengah sebelum menjejak supaya path tetap ringan;
hasilnya dipakai sebagai SVG inline sehingga warnanya bisa diatur lewat
CSS variables (--logo-ink / --logo-accent).
"""

from pathlib import Path

import cv2
import numpy as np
from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "storage/app/public/settings/G2aY7r8QvW9zwSClPdOMEe0gZuChmLcttJhG6Hoc.png"
OUTPUT = ROOT / "resources/views/partials/logo.blade.php"

INK = np.array([12, 12, 82])
ACCENT = np.array([255, 179, 0])
SCALE = 0.5
EPSILON = 1.0
MIN_AREA = 4


def trace(mask: np.ndarray) -> str:
    contours, _ = cv2.findContours(mask, cv2.RETR_CCOMP, cv2.CHAIN_APPROX_SIMPLE)
    chunks: list[str] = []
    for contour in contours:
        if cv2.contourArea(contour) < MIN_AREA:
            continue
        approx = cv2.approxPolyDP(contour, EPSILON, True)
        if len(approx) < 3:
            continue
        points = approx.reshape(-1, 2)
        chunks.append("M" + " ".join(f"{x},{y}" for x, y in points) + "Z")
    return " ".join(chunks)


def main() -> None:
    image = np.array(Image.open(SOURCE).convert("RGBA"))
    image = cv2.resize(
        image,
        (round(image.shape[1] * SCALE), round(image.shape[0] * SCALE)),
        interpolation=cv2.INTER_AREA,
    )

    rgb = image[:, :, :3].astype(np.int16)
    alpha = image[:, :, 3]

    ink_dist = np.abs(rgb - INK).sum(axis=2)
    accent_dist = np.abs(rgb - ACCENT).sum(axis=2)
    visible = alpha > 100

    ink_mask = ((ink_dist <= accent_dist) & visible).astype(np.uint8) * 255
    accent_mask = (accent_dist < ink_dist) & visible
    accent_mask = accent_mask.astype(np.uint8) * 255

    ink_path = trace(ink_mask)
    accent_path = trace(accent_mask)
    if not ink_path or not accent_path:
        raise SystemExit("Gagal menjejak salah satu warna logo.")

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    OUTPUT.write_text(
        "{{-- SVG inline: warnanya dikendalikan --logo-ink / --logo-accent di app.css. --}}\n"
        "@php($logoClass = $class ?? 'h-10 w-auto max-w-[10rem]')\n"
        "<svg class=\"logo-svg {{ $logoClass }}{{ empty($invert) ? '' : ' logo-on-dark' }}\"\n"
        f'     viewBox="0 0 {image.shape[1]} {image.shape[0]}" xmlns="http://www.w3.org/2000/svg"\n'
        '     role="img" aria-label="{{ $label ?? ($site[\'site_name\'] ?? config(\'app.name\', \'TikumPost\')) }}">\n'
        f'    <path class="logo-ink" fill-rule="evenodd" d="{ink_path}"/>\n'
        f'    <path class="logo-accent" fill-rule="evenodd" d="{accent_path}"/>\n'
        "</svg>\n",
        encoding="utf-8",
    )
    print(f"Ditulis {OUTPUT} ({OUTPUT.stat().st_size / 1024:.1f} KB)")


if __name__ == "__main__":
    main()
