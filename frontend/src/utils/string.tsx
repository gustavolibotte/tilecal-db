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
    if (!input || input.length < 14) return input;
    
    const suffix = input.substring(7);
    
    const cleanSuffix = suffix.replace(/[^0-9]/g, '');
    
    if (cleanSuffix.length !== 7) return input;

    if (regexPattern) {
        try {
            const cleanPattern = regexPattern.replace(/^\/|\/$/g, '');
            const regex = new RegExp(`^${cleanPattern}$`);
            
            if (!regex.test(input) && regex.test(cleanSuffix)) {
                return cleanSuffix; 
            }
        } catch (e) {
            console.error("Erro ao ler regex do equipamento:", e);
        }
    }

    return input;
};