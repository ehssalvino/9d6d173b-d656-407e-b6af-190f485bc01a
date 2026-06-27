"""Motor do Gerador de Memorial Solar."""

from .concessionaires import AllocationAccount, generate_concessionaire_package
from .core import GenerationError, GenerationResult, generate_memorial
from .single_line import DiagramError, DiagramResult, generate_single_line_diagram

__all__ = [
    "AllocationAccount",
    "GenerationError",
    "GenerationResult",
    "DiagramError",
    "DiagramResult",
    "generate_concessionaire_package",
    "generate_memorial",
    "generate_single_line_diagram",
]
