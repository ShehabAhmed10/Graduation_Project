document.addEventListener('DOMContentLoaded', function () {
    var color = document.getElementById('marker_color');
    var icon = document.getElementById('icon_name');
    var swatch = document.getElementById('preview-swatch');
    var iconPreview = document.getElementById('preview-icon');
    if (!swatch) return;
    if (color) color.addEventListener('input', function () { swatch.style.background = color.value; });
    if (icon) icon.addEventListener('input', function () { iconPreview.textContent = icon.value; });
});
