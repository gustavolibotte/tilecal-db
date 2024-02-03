import { ReactNode } from "react"

interface ListItemProps {
  children: ReactNode[] | ReactNode;
}

interface ListProps {
  children: ReactNode[] | ReactNode;
}

export function ListItem(props: ListItemProps) {
  return (
    <li
      className="flex py-3 px-2 border-top-1 surface-border flex-wrap">
      {props.children}
    </li>
  )
}

export function List(props: ListProps){
  return (
    <ul className="list-none p-0 m-0">
      {props.children}
    </ul>
  )
}
