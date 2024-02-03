import { ReactNode } from 'react';
import { TabView } from 'primereact/tabview';
import './tab_page.css';

interface TabPageProps {
    activeIndex: number;
    onTabChange: (e: any) => void;
    children: ReactNode[] | ReactNode;
} 

export default function TabPage(props: TabPageProps): ReactNode{

  return (
    <div className="card">
      <TabView
        activeIndex={props.activeIndex}
        onTabChange={props.onTabChange}>
          {props.children}
        </TabView>
    </div>
  );
}
