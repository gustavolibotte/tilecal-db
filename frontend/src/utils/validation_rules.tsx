import * as Yup from 'yup';

export const equipIdRules = Yup.string().matches(
  /^([A-Z0-9]{7}|[A-Z0-9]{14}|20LLCHI10V[0-9]{5})+$/,
  "Should be 7 or 14 characters long. Uppercase letters and numbers only"
);  // The extra condition 20LLCHI... refers to mainboard IDs that were issued with an extra character V

export const serialRules = Yup.string().max(
  36, "Serial max size is ${max} characters.").matches(
  /^[A-Za-z0-9 -_]*$/,
  'Serial code supports only alphanumeric, space, "-" and "_" characters.'
);

export const commentsRules = Yup.string().max(
  __MYSQL_MAX_TEXT_SIZE__, 'Comments max size is ${max} characters.'
);

export const fileDescriptionRules = commentsRules;

export const fileNameRules = Yup.string().max(
  100, 'File name max size is ${max} characters.'
);

export const filePathRules = Yup.string().max(
  300, 'File path max size is ${max} characters.'
).matches(
  /^https:\/\/cernbox.cern.ch\/.*$/,
  'Invalid CERNBox link.'
).url();

export const fpgaDnaRules = Yup.string().matches(
  /^[A-Za-z0-9]{24}$/,
  "Kintex ID (FPGA DNA) must be exactly 24 alphanumeric characters."
).nullable();

export const daughterboardSerialRules = Yup.string().matches(
  /^11[0-9]{5}$/,
  "Serial number must be exactly 7 digits and start with '11' (e.g. 1101001)."
).nullable();

export function extractSequentialNumber(id: string): string{
  if (id.length === 14){
    return id.substring(7, 14);
  }
  return id
}