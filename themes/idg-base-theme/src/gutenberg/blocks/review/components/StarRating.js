const StarRating = ({ rating }) => {
  if (rating === 0) {
    return null;
  }

  return (
    <>
      <h3 className="review-subTitle">Expert's Rating</h3>
      <div
        className="starRating"
        style={{ '--rating': `${rating}` }}
        ariaLabel={`Rating of this product is ${rating} out of 5.`}
      ></div>
    </>
  );
};

export default StarRating;
