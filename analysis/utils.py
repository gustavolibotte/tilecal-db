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
