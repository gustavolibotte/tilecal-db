export function snakeToSpaced(str: string): string{
    if (str === 'fpga_dna_a') return 'Kintex ID (FPGA DNA) A';
    if (str === 'fpga_dna_b') return 'Kintex ID (FPGA DNA) B';
    if (str === 'serial_number') return 'Board Serial Number';

    let words = str.split('_');
    words = words.map((word)=>{ return capitalize(word); });
    const newString = words.join(' '); 
    return newString;
}

export function capitalize(str: string): string{
    return str.charAt(0).toUpperCase()+str.slice(1);
}

export function makeSnakeCase(str: string): string{
    return str.toLowerCase().replace(' ', '_');
}