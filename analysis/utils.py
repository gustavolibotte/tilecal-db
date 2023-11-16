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
