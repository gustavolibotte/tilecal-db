export const drawerTypes = ['Mini Drawer', 'Mini Drawer (Standard)', 'Mini Drawer (With Patch-Panel)',
  'Micro Drawer 4', 'Micro Drawer 5', 'Micro Drawer 6', 'Micro Drawer 7'];

export function scanId(value: string): string | null {
  const id = value.trim();
  if (/^[A-Z0-9]{7}$/.test(id)) return id;
  if (/^20LLCHI10V[0-9]{5}$/.test(id)) return id.replace('V', '').slice(-7);
  if (/^[A-Z0-9]{14}$/.test(id)) return id.slice(-7);
  return null;
}

export function scanPosition(value: string): number | null {
  if (!/^\d{1,2}$/.test(value.trim())) return null;
  const number = Number(value.trim());
  return number >= 1 && number <= 48 ? number : null;
}

export function focusNextScanField(id: string) {
  const current = document.getElementById(id);
  const form = current?.closest('form');
  if (!current || !form || document.activeElement !== current) return;
  const fields = Array.from(form.querySelectorAll<HTMLElement>('[data-assembly-field]:not(:disabled)'));
  const next = fields[fields.indexOf(current) + 1];
  next?.focus();
  if (next instanceof HTMLInputElement) next.select();
}
