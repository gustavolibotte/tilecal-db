import PageLayout from '../page_layout/PageLayout';
import './not_found.css'

interface NotFoundProps {
  useLayout?: boolean
}
export default function NotFound(props: NotFoundProps){
  const baseNode = (
    <div className="jumbotron text-center align-items-center">
      <h1>This page was not found</h1>
      <p>This page was not found, please check if the link is valid. If it should be, please contact an admin.</p>
    </div>
  );
  if (props.useLayout){
    return (
      <PageLayout>
        {baseNode}
      </PageLayout>
    )
  }
  return baseNode;
}
