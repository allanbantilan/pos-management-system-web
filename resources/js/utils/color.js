export const isHexColor = (value) => /^#[0-9A-F]{6}$/i.test(String(value ?? ''));

export const hexToRgba = (hex, alpha = 1) => {
    if (!isHexColor(hex)) {
        return `rgba(234, 88, 12, ${alpha})`;
    }

    const normalized = hex.replace('#', '');
    const r = Number.parseInt(normalized.slice(0, 2), 16);
    const g = Number.parseInt(normalized.slice(2, 4), 16);
    const b = Number.parseInt(normalized.slice(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
};
