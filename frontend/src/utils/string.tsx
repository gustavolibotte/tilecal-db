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

export const applyAtlasBarcodeMask = (input: string, regexPattern?: string | null): string => {
    if (!input || input.length !== 14) return input;
    
    const isAtlasBarcode = /^[A-Za-z0-9]{14}$/.test(input);
    if (!isAtlasBarcode) return input;

    const sequential = input.substring(7, 14);

    if (regexPattern) {
        try {
            const cleanPattern = regexPattern.replace(/^\/|\/$/g, '');
            const regex = new RegExp(`^${cleanPattern}$`);
            
            if (!regex.test(input) && regex.test(sequential)) {
                return sequential; 
            }
        } catch (e) {
            console.error("Erro ao ler regex do equipamento:", e);
        }
    }

    return input;
};