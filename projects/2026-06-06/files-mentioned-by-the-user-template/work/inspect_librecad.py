from pathlib import Path
import time

from pywinauto import Application, Desktop


LIBRECAD = Path(r"C:\Program Files\LibreCAD\LibreCAD.exe")
DWG = Path(
    r"G:\.shortcut-targets-by-id\1mPCXWi3wET6kGb9N2tJ48BQTv4yE97tD"
    r"\26181  SONIA ALMEIDA\AUTOCAD -5.85 KWp-SONIA MEIRELLES ALMEIDA.dwg"
)

app = Application(backend="uia").start(f'"{LIBRECAD}" "{DWG}"')
time.sleep(35)
for window in app.windows():
    print(repr(window.window_text()), window.class_name())
    if window.class_name() == "QC_ApplicationWindow":
        window.capture_as_image().save(
            r"C:\Users\Eduardo\Documents\Codex\2026-06-06"
            r"\files-mentioned-by-the-user-template\work\librecad-dwg.png"
        )
        for control in window.descendants():
            text = control.window_text().strip()
            if text:
                print(control.element_info.control_type, repr(text[:200]))
print("DESKTOP")
for window in Desktop(backend="uia").windows():
    title = window.window_text()
    if "LibreCAD" in title or "initial" in title.lower():
        print(repr(title), window.class_name())
app.kill()
