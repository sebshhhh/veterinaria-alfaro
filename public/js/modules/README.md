# JavaScript por modulo

Esta carpeta contiene los scripts propios de cada modulo visible del sistema.

Criterio:

- Un archivo por modulo o pantalla principal.
- Las vistas Blade cargan solo el script que necesitan.
- El JavaScript global se mantiene en `resources/js/core` y se compila con Vite.

No colocar aqui dependencias externas ni codigo generado por Vite.
