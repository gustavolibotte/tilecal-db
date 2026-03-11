export const exportChartAsImage = (chartId: string, filename: string, format: 'PNG' | 'PDF') => {
  const chartElement = document.getElementById(chartId);
  if (!chartElement) return;
  
  const svg = chartElement.querySelector('svg');
  if (!svg) return;

  const svgData = new XMLSerializer().serializeToString(svg);
  const canvas = document.createElement('canvas');
  const img = new Image();
  
  img.onload = () => {
    canvas.width = img.width * 2; 
    canvas.height = img.height * 2;
    const ctx = canvas.getContext('2d');
    if (ctx) {
      ctx.scale(2, 2);
      ctx.fillStyle = "white";
      ctx.fillRect(0, 0, img.width, img.height);
      ctx.drawImage(img, 0, 0);
    }
    
    if (format === 'PNG') {
      const a = document.createElement('a');
      a.download = `${filename}.png`;
      a.href = canvas.toDataURL('image/png');
      a.click();
    } else if (format === 'PDF') {
      const printWindow = window.open('', '_blank');
      if (printWindow) {
        printWindow.document.write(`
          <html>
            <head><title>Export PDF - ${filename}</title></head>
            <body style="margin:0; display:flex; justify-content:center; align-items:center; height:100vh; background:white;">
              <img src="${canvas.toDataURL('image/png')}" style="max-width:100%;" />
              <script>
                setTimeout(() => { window.print(); window.close(); }, 300);
              </script>
            </body>
          </html>
        `);
        printWindow.document.close();
      }
    }
  };
  
  img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
};