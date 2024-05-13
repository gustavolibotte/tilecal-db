import * as Yup from 'yup';

export const equipIdRules = Yup.string().matches(
  /^([A-Z0-9]{7}|[A-Z0-9]{14})+$/,
  "Should be 7 or 14 characters long. Uppercase letters and numbers only"
);

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


export function extractSequentialNumber(id: string): string{
  if (id.length === 14){
    return id.substring(7, 14);
  }
  return id
}