from pathlib import Path

from PIL import Image


PROJECT = Path(__file__).resolve().parent
source = PROJECT / "assets" / "logo.png"
destination = PROJECT / "assets" / "delfos.ico"

logo = Image.open(source).convert("RGBA")
logo.thumbnail((220, 220), Image.Resampling.LANCZOS)

canvas = Image.new("RGBA", (256, 256), (255, 255, 255, 0))
x = (canvas.width - logo.width) // 2
y = (canvas.height - logo.height) // 2
canvas.alpha_composite(logo, (x, y))
canvas.save(destination, sizes=[(16, 16), (24, 24), (32, 32), (48, 48), (64, 64), (128, 128), (256, 256)])

print(destination)
