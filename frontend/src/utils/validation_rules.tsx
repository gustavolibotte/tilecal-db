import * as Yup from 'yup';

export const equipIdRules = Yup.object({
  parent_id: Yup.string().required("The field is required").matches(
    /^([A-Z0-9]{7}|[A-Z0-9]{14})+$/,
    "Should be 7 or 14 characters long. Uppercase letters and numbers only"
  )
});