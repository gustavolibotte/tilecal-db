import os
import pandas as pd


def is_unique(col: str, df: pd.DataFrame):
    has_duplicates = df.duplicated(col).any()
    if has_duplicates:
        return df[col].value_counts()
    else:
        return True


def match_regex(col: str, regex: str, df: pd.DataFrame):
    is_match = df[col].str.match(regex)
    if is_match.all():
        return True
    else:
        return is_match


def greater_than(col: str, value: float, df: pd.DataFrame):
    is_gt = df[col] > value
    if is_gt.all():
        return True
    else:
        return is_gt


def make_snake_case(string: str) -> str:
    return string.lower().replace(' ', '_')


def is_empty(var, bool_cast=False) -> bool:
    if pd.isna(var):
        return True
    elif isinstance(var, str) or isinstance(var, list):
        return not bool(var)
    elif bool_cast:
        return not bool(var)
    else:
        return False


def dump_in_batches(df, filename, output_dir, chunksize):
    for i, chunk_start in enumerate(range(0, len(df), chunksize)):
        data_to_save = df.iloc[chunk_start:chunk_start+chunksize]
        chunkfilepath = os.path.join(
            output_dir,
            f'{filename}_{i:03d}.csv'
        )
        data_to_save.to_csv(chunkfilepath, index=False)


def generate_mysql_insert_string(df, table_name):
    column_str = f"({', '.join(df.columns)})"
    insert_string = f"INSERT INTO {table_name} {column_str} VALUES\n"
    for _, row in df.iterrows():
        row_values = ', '.join([
            'NULL' if pd.isna(row[col]) else f"'{row[col]}'" 
            for col in df.columns])
        insert_string += f"({row_values}),\n"
    return insert_string[:-2] + ';'
